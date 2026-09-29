<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Leave\StoreLeaveRequest;
use App\Http\Resources\V1\LeaveBalanceResource;
use App\Http\Resources\V1\LeaveRequestResource;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveApiController extends BaseApiController
{
    /**
     * Dapatkan sisa kuota saldo cuti karyawan.
     */
    public function balances(Request $request, LeaveService $leaveService): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return $this->sendError('Profil karyawan tidak ditemukan.', null, 404);
        }

        $year = Carbon::now()->year;
        $leaveTypes = LeaveType::all();

        foreach ($leaveTypes as $type) {
            $leaveService->getOrCreateBalance($employee, $type, $year);
        }

        $balances = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', $year)
            ->with('leaveType')
            ->get();

        return $this->sendSuccess(
            LeaveBalanceResource::collection($balances),
            'Data saldo cuti berhasil dimuat.'
        );
    }

    /**
     * Ambil riwayat permohonan cuti karyawan saat ini.
     */
    public function index(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return $this->sendError('Profil karyawan tidak ditemukan.', null, 404);
        }

        $requests = LeaveRequest::where('employee_id', $employee->id)
            ->with('leaveType')
            ->orderByDesc('created_at')
            ->get();

        return $this->sendSuccess(
            LeaveRequestResource::collection($requests),
            'Riwayat permohonan cuti berhasil dimuat.'
        );
    }

    /**
     * Ajukan permohonan cuti baru via Mobile API.
     */
    public function store(StoreLeaveRequest $request, LeaveService $leaveService): JsonResponse
    {
        $employee = $request->user()->employee;
        $attachment = $request->file('attachment');

        $leaveRequest = $leaveService->submitLeaveRequest(
            $employee,
            $request->validated(),
            $attachment
        );

        $leaveRequest->load('leaveType');

        return $this->sendSuccess(
            new LeaveRequestResource($leaveRequest),
            'Permohonan cuti berhasil diajukan.',
            201
        );
    }

    /**
     * Ambil daftar pengajuan cuti yang membutuhkan persetujuan (Khusus Manager & HR Admin).
     */
    public function approvals(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        if (! $employee) {
            return $this->sendError('Profil karyawan tidak ditemukan.', null, 404);
        }

        $query = LeaveRequest::query()
            ->with(['employee.user', 'leaveType'])
            ->where('status', 'PENDING');

        if ($user->hasRole('manager') && ! $user->hasAnyRole(['super_admin', 'hr_admin'])) {
            $query->whereHas('employee', function ($q) use ($employee): void {
                $q->where('manager_id', $employee->id);
            });
        }

        $pendingRequests = $query->orderByDesc('created_at')->get();

        return $this->sendSuccess(
            LeaveRequestResource::collection($pendingRequests),
            'Daftar persetujuan cuti berhasil dimuat.'
        );
    }

    /**
     * Proses persetujuan (Approve / Reject) permohonan cuti via Mobile API.
     */
    public function approve(Request $request, int $id, LeaveService $leaveService): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:APPROVED,REJECTED'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $approver = $request->user()->employee;
        if (! $approver) {
            return $this->sendError('Profil peninjau tidak ditemukan.', null, 404);
        }

        $leaveRequest = LeaveRequest::with(['employee', 'leaveType'])->findOrFail($id);

        if ($request->input('status') === 'APPROVED') {
            $leaveService->approve($leaveRequest, $approver, $request->input('notes'));
            $message = 'Permohonan cuti berhasil disetujui.';
        } else {
            $leaveService->reject($leaveRequest, $approver, $request->input('notes'));
            $message = 'Permohonan cuti telah ditolak.';
        }

        return $this->sendSuccess(
            new LeaveRequestResource($leaveRequest->fresh(['leaveType', 'employee.user'])),
            $message
        );
    }
}
