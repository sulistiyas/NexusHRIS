<?php

namespace App\Http\Controllers;

use App\Http\Requests\Leave\ProcessApprovalRequest;
use App\Models\ActivityLog;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveApprovalController extends Controller
{
    public function __construct(protected LeaveService $leaveService) {}

    /**
     * Tampilkan daftar pengajuan cuti bawahan yang menunggu approval.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee;

        $query = LeaveRequest::query()->with([
            'employee.user',
            'employee.department',
            'leaveType',
            'approvals.approver.user',
        ]);

        // Jika Manager (bukan HR atau Super Admin), tampilkan pengajuan bawahan di mana ia adalah approver
        if ($user->hasRole('manager') && ! $user->hasAnyRole(['super_admin', 'hr_admin'])) {
            $query->whereHas('approvals', function ($q) use ($employee): void {
                $q->where('approver_id', $employee?->id)
                    ->where('status', 'PENDING');
            });
        } else {
            // HR Admin & Super Admin melihat semua permohonan yang aktif
            $query->whereIn('status', ['PENDING', 'APPROVED', 'REJECTED']);
        }

        $leaveRequests = $query->orderByDesc('created_at')->paginate(15);

        return view('leave-approvals.index', compact('leaveRequests'));
    }

    /**
     * Tampilkan detail permohonan cuti untuk diproses approval.
     */
    public function show(LeaveRequest $leaveRequest): View
    {
        $leaveRequest->load([
            'employee.user',
            'employee.department',
            'leaveType',
            'approvals.approver.user',
        ]);

        return view('leave-approvals.show', compact('leaveRequest'));
    }

    /**
     * Setujui permohonan cuti (Approve).
     */
    public function approve(ProcessApprovalRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $approver = $request->user()->employee;
        if (! $approver) {
            return back()->with('error', 'Profil karyawan peninjau tidak ditemukan.');
        }

        try {
            $this->leaveService->approve($leaveRequest, $approver, $request->input('remarks'));

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'event' => 'leave_request_approved',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => ['leave_request_id' => $leaveRequest->id],
            ]);

            return redirect()->route('leave-approvals.index')
                ->with('success', 'Permohonan cuti berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tolak permohonan cuti (Reject).
     */
    public function reject(ProcessApprovalRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $approver = $request->user()->employee;
        if (! $approver) {
            return back()->with('error', 'Profil karyawan peninjau tidak ditemukan.');
        }

        $this->leaveService->reject($leaveRequest, $approver, $request->input('remarks'));

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'leave_request_rejected',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => ['leave_request_id' => $leaveRequest->id],
        ]);

        return redirect()->route('leave-approvals.index')
            ->with('success', 'Permohonan cuti telah ditolak.');
    }
}
