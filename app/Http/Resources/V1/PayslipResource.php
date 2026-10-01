<?php

namespace App\Http\Resources\V1;

use App\Models\Payslip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payslip
 */
class PayslipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slip_number' => $this->slip_number,
            'period' => [
                'month' => $this->payrollBatch->month ?? null,
                'year' => $this->payrollBatch->year ?? null,
                'label' => $this->payrollBatch ? Carbon::create($this->payrollBatch->year, $this->payrollBatch->month, 1)->translatedFormat('F Y') : null,
                'payment_date' => $this->payrollBatch->payment_date?->toDateString(),
            ],
            'financials' => [
                'basic_salary' => (float) $this->basic_salary,
                'total_allowances' => (float) $this->total_allowances,
                'total_overtime_pay' => (float) $this->total_overtime_pay,
                'gross_salary' => (float) ($this->basic_salary + $this->total_allowances + $this->total_overtime_pay),
                'total_deductions' => (float) $this->total_deductions,
                'net_salary' => (float) $this->net_salary,
            ],
            'bank_account_no' => $this->bank_account_no,
            'status' => $this->payrollBatch->status ?? 'DRAFT',
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(fn ($item) => [
                    'id' => $item->id,
                    'component_name' => $item->component_name,
                    'component_type' => $item->component_type,
                    'amount' => (float) $item->amount,
                ]);
            }),
            'pdf_download_url' => route('api.payslips.download', $this->id),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
