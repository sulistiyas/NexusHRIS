<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveApproval;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    /**
     * Hitung total hari kerja (Senin - Jumat) di antara dua tanggal.
     */
    public function calculateWorkingDays(Carbon $startDate, Carbon $endDate): int
    {
        if ($startDate->greaterThan($endDate)) {
            return 0;
        }

        return (int) $startDate->diffInDaysFiltered(
            fn (Carbon $date) => ! $date->isWeekend(),
            $endDate->copy()->addDay()
        );
    }

    /**
     * Ambil atau inisialisasi saldo cuti karyawan untuk tahun tertentu.
     */
    public function getOrCreateBalance(Employee $employee, LeaveType $leaveType, int $year): LeaveBalance
    {
        return LeaveBalance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'year' => $year,
            ],
            [
                'total_entitled' => $leaveType->default_days_per_year,
                'used_days' => 0,
                'remaining_days' => $leaveType->default_days_per_year,
            ]
        );
    }

    /**
     * Ajukan permohonan cuti baru.
     */
    public function submitLeaveRequest(Employee $employee, array $data, ?UploadedFile $attachment = null): LeaveRequest
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $workingDays = $this->calculateWorkingDays($startDate, $endDate);

        if ($workingDays <= 0) {
            throw ValidationException::withMessages([
                'end_date' => 'Rentang tanggal cuti tidak memuat hari kerja aktif (Senin - Jumat).',
            ]);
        }

        $leaveType = LeaveType::findOrFail($data['leave_type_id']);

        // 1. Cek Kuota Saldo jika memotong cuti tahunan
        if ($leaveType->is_deduct_annual) {
            $balance = $this->getOrCreateBalance($employee, $leaveType, $startDate->year);
            if ($balance->remaining_days < $workingDays) {
                throw ValidationException::withMessages([
                    'start_date' => "Sisa saldo cuti Anda tidak mencukupi (sisa: {$balance->remaining_days} hari, diajukan: {$workingDays} hari).",
                ]);
            }
        }

        // 2. Simpan lampiran jika ada
        $attachmentPath = null;
        if ($attachment) {
            $attachmentPath = $attachment->store('leaves/attachments', 'public');
        }

        // 3. Simpan record leave_request
        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_days' => $workingDays,
            'reason' => $data['reason'],
            'attachment_path' => $attachmentPath,
            'status' => 'PENDING',
        ]);

        // 4. Inisialisasi Alur Persetujuan Level 1 (Atasan Langsung)
        $approverId = $employee->manager_id ?? $this->findDefaultHrAdminEmployeeId();

        if ($approverId) {
            LeaveApproval::create([
                'leave_request_id' => $leaveRequest->id,
                'approver_id' => $approverId,
                'approval_level' => 1,
                'status' => 'PENDING',
            ]);
        }

        return $leaveRequest;
    }

    /**
     * Proses Persetujuan (Approve) Bertingkat.
     */
    public function approve(LeaveRequest $leaveRequest, Employee $approver, ?string $remarks = null): void
    {
        $currentApproval = $leaveRequest->approvals()
            ->where('status', 'PENDING')
            ->orderBy('approval_level')
            ->first();

        if (! $currentApproval) {
            throw new \DomainException('Tidak ada tahapan persetujuan yang menunggu tindakan.');
        }

        // Tandai tahap saat ini disetujui
        $currentApproval->update([
            'status' => 'APPROVED',
            'remarks' => $remarks,
            'acted_at' => Carbon::now(),
        ]);

        // Tentukan apakah masih ada Level 2 (HR Admin)
        if ($currentApproval->approval_level === 1 && $leaveRequest->employee->manager_id) {
            $hrApproverId = $this->findDefaultHrAdminEmployeeId();

            // Jika HR Admin berbeda dari atasan langsung, buat tahapan Level 2
            if ($hrApproverId && $hrApproverId !== $currentApproval->approver_id) {
                LeaveApproval::create([
                    'leave_request_id' => $leaveRequest->id,
                    'approver_id' => $hrApproverId,
                    'approval_level' => 2,
                    'status' => 'PENDING',
                ]);

                return;
            }
        }

        // Jika sampai di sini, maka ini adalah Final Approval
        $leaveRequest->update([
            'status' => 'APPROVED',
            'final_approved_by' => $approver->id,
            'final_approved_at' => Carbon::now(),
        ]);

        // Potong Saldo Cuti jika tipe cuti memotong kuota tahunan
        if ($leaveRequest->leaveType->is_deduct_annual) {
            $year = Carbon::parse($leaveRequest->start_date)->year;
            $balance = $this->getOrCreateBalance($leaveRequest->employee, $leaveRequest->leaveType, $year);

            $balance->increment('used_days', $leaveRequest->total_days);
            $balance->decrement('remaining_days', $leaveRequest->total_days);
        }
    }

    /**
     * Tolak Permohonan Cuti (Reject).
     */
    public function reject(LeaveRequest $leaveRequest, Employee $approver, ?string $remarks = null): void
    {
        $currentApproval = $leaveRequest->approvals()
            ->where('status', 'PENDING')
            ->orderBy('approval_level')
            ->first();

        if ($currentApproval) {
            $currentApproval->update([
                'status' => 'REJECTED',
                'remarks' => $remarks,
                'acted_at' => Carbon::now(),
            ]);
        }

        $leaveRequest->update([
            'status' => 'REJECTED',
        ]);
    }

    /**
     * Cari ID Karyawan yang memegang role HR Admin untuk persetujuan Level 2.
     */
    protected function findDefaultHrAdminEmployeeId(): ?int
    {
        return Employee::whereHas('user', fn ($q) => $q->role('hr_admin'))->value('id')
            ?? Employee::whereHas('user', fn ($q) => $q->role('super_admin'))->value('id');
    }
}
