<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'date' => $this->date instanceof \DateTimeInterface ? $this->date->format('Y-m-d') : $this->date,
            'clock_in' => $this->clock_in,
            'clock_out' => $this->clock_out,
            'in_latitude' => $this->in_latitude !== null ? (float) $this->in_latitude : null,
            'in_longitude' => $this->in_longitude !== null ? (float) $this->in_longitude : null,
            'out_latitude' => $this->out_latitude !== null ? (float) $this->out_latitude : null,
            'out_longitude' => $this->out_longitude !== null ? (float) $this->out_longitude : null,
            'in_selfie_path' => $this->in_selfie_path,
            'out_selfie_path' => $this->out_selfie_path,
            'work_type' => $this->work_type,
            'status' => $this->status,
            'late_minutes' => (int) ($this->late_minutes ?? 0),
            'early_leave_minutes' => (int) ($this->early_leave_minutes ?? 0),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
