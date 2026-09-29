<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ClockOutRequest extends FormRequest
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
            'selfie.required' => 'Foto selfie absensi pulang wajib diambil.',
        ];
    }
}
