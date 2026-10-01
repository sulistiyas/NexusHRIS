<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CashAdvance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashAdvanceApprovalController extends Controller
{
    /**
     * Tampilkan seluruh permohonan kasbon karyawan untuk HR/Super Admin.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = CashAdvance::with(['employee.user', 'employee.department', 'employee.designation'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($q, $term) {
                $q->where('request_number', 'like', "%{$term}%")
                    ->orWhereHas('employee.user', fn ($u) => $u->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('employee', fn ($e) => $e->where('employee_code', 'like', "%{$term}%"));
            });

        $cashAdvances = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $countPending = CashAdvance::where('status', 'PENDING')->count();
        $countActive = CashAdvance::where('status', 'ACTIVE')->count();

        return view('cash-advances.index', compact('cashAdvances', 'status', 'search', 'countPending', 'countActive'));
    }

    /**
     * Setujui permohonan kasbon menjadi pinjaman aktif (ACTIVE).
     */
    public function approve(Request $request, CashAdvance $cashAdvance): RedirectResponse
    {
        if ($cashAdvance->status !== 'PENDING') {
            return back()->with('error', 'Permohonan ini sudah diproses sebelumnya.');
        }

        $cashAdvance->update([
            'status' => 'ACTIVE',
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'cash_advance_approved',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => ['request_number' => $cashAdvance->request_number, 'amount' => $cashAdvance->amount],
        ]);

        return redirect()->route('cash-advance-approvals.index')
            ->with('success', "Permohonan kasbon {$cashAdvance->request_number} disetujui dan telah berstatus AKTIF.");
    }

    /**
     * Tolak permohonan kasbon (REJECTED).
     */
    public function reject(Request $request, CashAdvance $cashAdvance): RedirectResponse
    {
        if ($cashAdvance->status !== 'PENDING') {
            return back()->with('error', 'Permohonan ini sudah diproses sebelumnya.');
        }

        $cashAdvance->update([
            'status' => 'REJECTED',
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'cash_advance_rejected',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => ['request_number' => $cashAdvance->request_number],
        ]);

        return redirect()->route('cash-advance-approvals.index')
            ->with('success', "Permohonan kasbon {$cashAdvance->request_number} telah ditolak.");
    }
}
