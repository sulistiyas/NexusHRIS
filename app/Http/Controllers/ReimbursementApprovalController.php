<?php

namespace App\Http\Controllers;

use App\Actions\Reimbursement\DisburseReimbursementAction;
use App\Models\ActivityLog;
use App\Models\Reimbursement;
use App\Models\ReimbursementAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReimbursementApprovalController extends Controller
{
    /**
     * Display a listing of reimbursement claims for approval & disbursement.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status', 'ALL');
        $search = $request->input('search');

        $query = Reimbursement::with(['employee.department', 'employee.designation', 'category', 'attachments'])
            ->latest('claim_date');

        if ($status !== 'ALL') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('claim_number', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($empQ) use ($search) {
                        $empQ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%");
                    });
            });
        }

        $reimbursements = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Reimbursement::count(),
            'pending' => Reimbursement::where('status', 'PENDING')->count(),
            'approved' => Reimbursement::where('status', 'APPROVED')->count(),
            'disbursed' => Reimbursement::where('status', 'DISBURSED')->count(),
            'rejected' => Reimbursement::where('status', 'REJECTED')->count(),
        ];

        return view('reimbursements.index', compact('reimbursements', 'status', 'search', 'stats'));
    }

    /**
     * Display detail of a reimbursement claim.
     */
    public function show(Reimbursement $reimbursement): View
    {
        $reimbursement->load(['employee.department', 'employee.designation', 'employee.branch', 'category', 'approvedBy', 'attachments']);

        return view('reimbursements.show', compact('reimbursement'));
    }

    /**
     * Approve a pending reimbursement claim.
     */
    public function approve(Request $request, Reimbursement $reimbursement): RedirectResponse
    {
        if ($reimbursement->status !== 'PENDING') {
            return back()->with('error', 'Hanya pengajuan berstatus PENDING yang dapat disetujui.');
        }

        $approverEmployee = $request->user()->employee;

        $reimbursement->update([
            'status' => 'APPROVED',
            'approved_by' => $approverEmployee?->id,
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'reimbursement_approved',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'claim_number' => $reimbursement->claim_number,
                'total_amount' => $reimbursement->total_amount,
                'approved_by' => $approverEmployee?->id,
            ],
        ]);

        return redirect()->route('reimbursement-approvals.index')
            ->with('success', "Klaim {$reimbursement->claim_number} berhasil disetujui dan menunggu pencairan (Disbursement) Finance.");
    }

    /**
     * Reject a pending reimbursement claim.
     */
    public function reject(Request $request, Reimbursement $reimbursement): RedirectResponse
    {
        if ($reimbursement->status !== 'PENDING') {
            return back()->with('error', 'Hanya pengajuan berstatus PENDING yang dapat ditolak.');
        }

        $reimbursement->update([
            'status' => 'REJECTED',
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'reimbursement_rejected',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'claim_number' => $reimbursement->claim_number,
                'status' => 'REJECTED',
            ],
        ]);

        return redirect()->route('reimbursement-approvals.index')
            ->with('success', "Klaim {$reimbursement->claim_number} telah ditolak.");
    }

    /**
     * Disburse an approved reimbursement claim.
     */
    public function disburse(Request $request, Reimbursement $reimbursement, DisburseReimbursementAction $action): RedirectResponse
    {
        if ($reimbursement->status !== 'APPROVED') {
            return back()->with('error', 'Hanya klaim berstatus APPROVED yang dapat dicairkan.');
        }

        $action->execute(
            reimbursement: $reimbursement,
            userId: $request->user()->id,
            ip: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()->route('reimbursement-approvals.index')
            ->with('success', "Dana klaim {$reimbursement->claim_number} sebesar Rp ".number_format((float) $reimbursement->total_amount, 0, ',', '.').' berhasil dicairkan (DISBURSED).');
    }

    /**
     * Download attachment file securely.
     */
    public function downloadAttachment(ReimbursementAttachment $attachment): StreamedResponse
    {
        if (! Storage::disk('private')->exists($attachment->file_path)) {
            abort(404, 'Berkas kuitansi tidak ditemukan di penyimpanan server.');
        }

        return Storage::disk('private')->download($attachment->file_path, $attachment->file_name);
    }
}
