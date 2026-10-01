<?php

namespace App\Http\Resources\V1;

use App\Models\Reimbursement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Reimbursement
 */
class ReimbursementResource extends JsonResource
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
            'claim_number' => $this->claim_number,
            'claim_date' => $this->claim_date?->format('Y-m-d'),
            'total_amount' => (float) $this->total_amount,
            'description' => $this->description,
            'status' => $this->status,
            'category' => new ReimbursementCategoryResource($this->whenLoaded('category')),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'approved_by' => new EmployeeResource($this->whenLoaded('approvedBy')),
            'disbursed_at' => $this->disbursed_at?->toISOString(),
            'attachments' => $this->attachments->map(fn ($attachment) => [
                'id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'download_url' => route('reimbursements.attachments.download', $attachment->id),
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
