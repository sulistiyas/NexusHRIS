<?php

namespace App\Http\Controllers;

use App\Actions\Reimbursement\SubmitReimbursementAction;
use App\Http\Requests\StoreReimbursementRequest;
use App\Models\Reimbursement;
use App\Models\ReimbursementAttachment;
use App\Models\ReimbursementCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EssReimbursementController extends Controller
{
    /**
     * Display a listing of the employee's reimbursement claims.
     */
    public function index(Request $request): View
    {
        $employee = $request->user()->employee;

        $reimbursements = Reimbursement::with(['category', 'attachments'])
            ->where('employee_id', $employee?->id)
            ->latest('claim_date')
            ->paginate(10);

        return view('ess.reimbursements.index', compact('reimbursements'));
    }

    /**
     * Show the form for creating a new reimbursement claim.
     */
    public function create(): View
    {
        $categories = ReimbursementCategory::orderBy('name')->get();

        return view('ess.reimbursements.create', compact('categories'));
    }

    /**
     * Store a newly created reimbursement claim in storage.
     */
    public function store(StoreReimbursementRequest $request, SubmitReimbursementAction $action): RedirectResponse
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return back()->with('error', 'Data profil karyawan Anda tidak ditemukan.');
        }

        $files = $request->file('receipts') ?? $request->file('receipt') ?? $request->file('receipt_image');

        $reimbursement = $action->execute(
            employee: $employee,
            data: $request->validated(),
            files: $files,
            userId: $request->user()->id,
            ip: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()->route('ess.reimbursements.index')
            ->with('success', "Klaim biaya berhasil diajukan dengan nomor referensi {$reimbursement->claim_number}.");
    }

    /**
     * Display the specified reimbursement claim.
     */
    public function show(Request $request, Reimbursement $reimbursement): View
    {
        $employee = $request->user()->employee;

        if ($reimbursement->employee_id !== $employee?->id && ! $request->user()->hasRole(['super_admin', 'hr_admin', 'manager'])) {
            abort(403, 'Anda tidak berhak melihat klaim reimbursement ini.');
        }

        $reimbursement->load(['category', 'employee', 'approvedBy', 'attachments']);

        return view('ess.reimbursements.show', compact('reimbursement'));
    }

    /**
     * Download the attachment file.
     */
    public function downloadAttachment(Request $request, ReimbursementAttachment $attachment): StreamedResponse
    {
        $reimbursement = $attachment->reimbursement;
        $employee = $request->user()->employee;

        if ($reimbursement->employee_id !== $employee?->id && ! $request->user()->hasRole(['super_admin', 'hr_admin', 'manager'])) {
            abort(403, 'Akses ke berkas lampiran ditolak.');
        }

        if (! Storage::disk('private')->exists($attachment->file_path)) {
            abort(404, 'Berkas kuitansi tidak ditemukan di penyimpanan server.');
        }

        return Storage::disk('private')->download($attachment->file_path, $attachment->file_name);
    }
}
