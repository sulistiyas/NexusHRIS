<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OvertimeApprovalController extends Controller
{
    /**
     * Tampilkan daftar pengajuan lembur yang menunggu persetujuan atasan.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee;

        $query = Overtime::query()->with(['employee.user', 'employee.department', 'approvedBy.user']);

        // Jika Manager, tampilkan pengajuan anggota timnya
        if ($user->hasRole('manager') && ! $user->hasAnyRole(['super_admin', 'hr_admin'])) {
            $query->whereHas('employee', function ($q) use ($employee): void {
                $q->where('manager_id', $employee?->id);
            });
        }

        $overtimes = $query->orderByDesc('date')->paginate(15);

        return view('overtime-approvals.index', compact('overtimes'));
    }

    /**
     * Setujui pengajuan lembur (Approve).
     */
    public function approve(Request $request, Overtime $overtime): RedirectResponse
    {
        $approver = $request->user()->employee;
        if (! $approver) {
            return back()->with('error', 'Profil peninjau tidak ditemukan.');
        }

        $overtime->update([
            'status' => 'APPROVED',
            'approved_by' => $approver->id,
            'approved_at' => Carbon::now(),
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'overtime_approved',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => ['overtime_id' => $overtime->id],
        ]);

        return redirect()->route('overtime-approvals.index')
            ->with('success', 'Permohonan lembur berhasil disetujui.');
    }

    /**
     * Tolak pengajuan lembur (Reject).
     */
    public function reject(Request $request, Overtime $overtime): RedirectResponse
    {
        $approver = $request->user()->employee;
        if (! $approver) {
            return back()->with('error', 'Profil peninjau tidak ditemukan.');
        }

        $overtime->update([
            'status' => 'REJECTED',
            'approved_by' => $approver->id,
            'approved_at' => Carbon::now(),
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'overtime_rejected',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => ['overtime_id' => $overtime->id],
        ]);

        return redirect()->route('overtime-approvals.index')
            ->with('success', 'Permohonan lembur telah ditolak.');
    }
}
