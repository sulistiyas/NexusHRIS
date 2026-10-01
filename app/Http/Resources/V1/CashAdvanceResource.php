<?php

namespace App\Http\Resources\V1;

use App\Models\CashAdvance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CashAdvance
 */
class CashAdvanceResource extends JsonResource
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
            'request_number' => $this->request_number,
            'amount' => (float) $this->amount,
            'reason' => $this->reason,
            'installment_months' => (int) $this->installment_months,
            'monthly_deduction' => (float) $this->monthly_deduction,
            'remaining_amount' => (float) $this->remaining_amount,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
