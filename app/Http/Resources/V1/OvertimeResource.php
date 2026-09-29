<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OvertimeResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'employee_name' => $this->employee?->user?->name,
            'date' => $this->date instanceof \DateTimeInterface ? $this->date->format('Y-m-d') : $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'total_hours' => (float) $this->total_hours,
            'reason' => $this->reason,
            'status' => $this->status,
            'approved_by_id' => $this->approved_by,
            'approved_by_name' => $this->approvedBy?->user?->name,
            'approved_at' => $this->approved_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
