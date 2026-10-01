<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreCashAdvanceRequest;
use App\Http\Resources\V1\CashAdvanceResource;
use App\Models\ActivityLog;
use App\Models\CashAdvance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashAdvanceApiController extends BaseApiController
{
    /**
     * Dapatkan riwayat permohonan kasbon dan sisa saldo pinjaman aktif.
     */
    public function index(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return $this->sendError('Profil karyawan tidak ditemukan.', null, 404);
        }

        $cashAdvances = CashAdvance::where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        $activeDebt = (float) CashAdvance::where('employee_id', $employee->id)
            ->where('status', 'ACTIVE')
            ->sum('remaining_amount');

        $hasPending = CashAdvance::where('employee_id', $employee->id)
            ->where('status', 'PENDING')
            ->exists();

        $data = CashAdvanceResource::collection($cashAdvances)->response()->getData(true);
        $data['summary'] = [
            'active_debt' => $activeDebt,
            'has_pending_request' => $hasPending,
        ];

        return $this->sendSuccess($data, 'Data pinjaman kasbon berhasil dimuat.');
    }

    /**
     * Kirim permohonan pinjaman kasbon baru melalui mobile app.
     */
    public function store(StoreCashAdvanceRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return $this->sendError('Profil karyawan tidak ditemukan.', null, 404);
        }

        $hasPending = CashAdvance::where('employee_id', $employee->id)
            ->where('status', 'PENDING')
            ->exists();

        if ($hasPending) {
            return $this->sendError('Anda masih memiliki permohonan kasbon berstatus Menunggu Persetujuan (PENDING).', null, 422);
        }

        $amount = (float) $request->validated('amount');
        $months = (int) $request->validated('installment_months');
        $monthlyDeduction = round($amount / $months, 2);

        $datePrefix = Carbon::now()->format('Ym');
        $seq = CashAdvance::where('request_number', 'like', "CA-{$datePrefix}-%")->count() + 1;
        $requestNumber = sprintf('CA-%s-%04d', $datePrefix, $seq);

        $cashAdvance = CashAdvance::create([
            'employee_id' => $employee->id,
            'request_number' => $requestNumber,
            'amount' => $amount,
            'reason' => $request->validated('reason'),
            'installment_months' => $months,
            'monthly_deduction' => $monthlyDeduction,
            'remaining_amount' => $amount,
            'status' => 'PENDING',
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'cash_advance_requested_api',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => ['request_number' => $requestNumber, 'amount' => $amount],
        ]);

        return $this->sendSuccess(
            new CashAdvanceResource($cashAdvance),
            "Permohonan kasbon {$requestNumber} berhasil diajukan.",
            201
        );
    }
}
