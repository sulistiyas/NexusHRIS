<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Overtime\StoreOvertimeRequest;
use App\Http\Resources\V1\OvertimeResource;
use App\Models\ActivityLog;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OvertimeApiController extends BaseApiController
{
    /**
     * Ambil riwayat pengajuan lembur karyawan saat ini.
     */
    public function index(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return $this->sendError('Profil karyawan tidak ditemukan.', null, 404);
        }

        $overtimes = Overtime::where('employee_id', $employee->id)
            ->with(['approvedBy.user'])
            ->orderByDesc('date')
            ->get();

        return $this->sendSuccess(
            OvertimeResource::collection($overtimes),
            'Riwayat permohonan lembur berhasil dimuat.'
        );
    }

    /**
     * Ajukan permohonan lembur baru via Mobile API.
     */
    public function store(StoreOvertimeRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        $data = $request->validated();

        $start = Carbon::parse("{$data['date']} {$data['start_time']}");
        $end = Carbon::parse("{$data['date']} {$data['end_time']}");

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $minutes = $start->diffInMinutes($end);
        if ($minutes < 30) {
            throw ValidationException::withMessages([
                'end_time' => 'Durasi lembur minimal 30 menit.',
            ]);
        }

        $totalHours = round($minutes / 60, 2);

        $overtime = Overtime::create([
            'employee_id' => $employee->id,
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'total_hours' => $totalHours,
            'reason' => $data['reason'],
            'status' => 'PENDING',
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'overtime_submitted_api',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'overtime_id' => $overtime->id,
                'total_hours' => $totalHours,
            ],
        ]);

        return $this->sendSuccess(
            new OvertimeResource($overtime),
            'Permohonan lembur berhasil diajukan.',
            201
        );
    }

    /**
     * Ambil daftar lembur tim yang menunggu verifikasi atasan.
     */
    public function approvals(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        if (! $employee) {
            return $this->sendError('Profil karyawan tidak ditemukan.', null, 404);
        }

        $query = Overtime::query()
            ->with(['employee.user', 'approvedBy.user'])
            ->where('status', 'PENDING');

        if ($user->hasRole('manager') && ! $user->hasAnyRole(['super_admin', 'hr_admin'])) {
            $query->whereHas('employee', function ($q) use ($employee): void {
                $q->where('manager_id', $employee->id);
            });
        }

        $pendingOvertimes = $query->orderByDesc('date')->get();

        return $this->sendSuccess(
            OvertimeResource::collection($pendingOvertimes),
            'Daftar verifikasi lembur tim berhasil dimuat.'
        );
    }

    /**
     * Verifikasi (Approve / Reject) lembur tim via Mobile API.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:APPROVED,REJECTED'],
        ]);

        $approver = $request->user()->employee;
        if (! $approver) {
            return $this->sendError('Profil peninjau tidak ditemukan.', null, 404);
        }

        $overtime = Overtime::with('employee.user')->findOrFail($id);

        $status = $request->input('status');
        $overtime->update([
            'status' => $status,
            'approved_by' => $approver->id,
            'approved_at' => Carbon::now(),
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'overtime_status_updated_api',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'overtime_id' => $overtime->id,
                'status' => $status,
            ],
        ]);

        $message = $status === 'APPROVED' ? 'Permohonan lembur berhasil disetujui.' : 'Permohonan lembur telah ditolak.';

        return $this->sendSuccess(
            new OvertimeResource($overtime->fresh(['approvedBy.user'])),
            $message
        );
    }
}
