<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
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
            'leave_type_id' => $this->leave_type_id,
            'leave_type_name' => $this->leaveType?->name,
            'start_date' => $this->start_date instanceof \DateTimeInterface ? $this->start_date->format('Y-m-d') : $this->start_date,
            'end_date' => $this->end_date instanceof \DateTimeInterface ? $this->end_date->format('Y-m-d') : $this->end_date,
            'total_days' => $this->total_days,
            'reason' => $this->reason,
            'status' => $this->status,
            'attachment_path' => $this->attachment_path,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
