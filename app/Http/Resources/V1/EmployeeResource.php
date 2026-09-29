<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'nik_ktp' => $this->nik_ktp,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'birth_place' => $this->birth_place,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar_url ? url($this->avatar_url) : null,
            'employment_status' => $this->employment_status,
            'join_date' => $this->join_date?->format('Y-m-d'),
            'contract_end_date' => $this->contract_end_date?->format('Y-m-d'),
            'branch' => [
                'id' => $this->branch_id,
                'name' => $this->branch?->name,
            ],
            'department' => [
                'id' => $this->department_id,
                'name' => $this->department?->name,
            ],
            'designation' => [
                'id' => $this->designation_id,
                'title' => $this->designation?->title,
            ],
            'manager' => $this->manager ? [
                'id' => $this->manager->id,
                'name' => $this->manager->user?->name,
            ] : null,
            'emergency_contacts' => EmergencyContactResource::collection($this->whenLoaded('emergencyContacts')),
        ];
    }
}
