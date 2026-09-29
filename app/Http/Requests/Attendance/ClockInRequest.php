<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ClockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->employee !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'work_type' => ['required', 'in:WFO,WFH'],
            'selfie' => ['required_without:selfie_image'],
            'selfie_image' => ['required_without:selfie'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'latitude.required' => 'Koordinat garis lintang (latitude) GPS tidak terdeteksi.',
            'longitude.required' => 'Koordinat garis bujur (longitude) GPS tidak terdeteksi.',
            'work_type.required' => 'Tipe kerja (WFO/WFH) wajib dipilih.',
            'selfie.required' => 'Foto selfie absensi wajib diambil melalui kamera.',
        ];
    }
}
