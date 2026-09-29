<?php

namespace App\Http\Requests\Shift;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diotorisasi untuk melakukan request ini.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['super_admin', 'hr_admin']) ?? false;
    }

    /**
     * Aturan validasi input shift baru.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'grace_period_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'is_night_shift' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Pesan error kustom bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama shift kerja wajib diisi.',
            'name.max' => 'Nama shift maksimal 50 karakter.',
            'start_time.required' => 'Jam masuk kerja wajib diisi.',
            'start_time.date_format' => 'Format jam masuk harus berupa HH:mm (contoh: 08:00).',
            'end_time.required' => 'Jam pulang kerja wajib diisi.',
            'end_time.date_format' => 'Format jam pulang harus berupa HH:mm (contoh: 17:00).',
            'grace_period_minutes.required' => 'Toleransi keterlambatan wajib diisi.',
            'grace_period_minutes.integer' => 'Toleransi keterlambatan harus berupa angka menit.',
            'grace_period_minutes.min' => 'Toleransi keterlambatan minimal 0 menit.',
            'grace_period_minutes.max' => 'Toleransi keterlambatan maksimal 120 menit (2 jam).',
        ];
    }

    /**
     * Persiapkan data sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_night_shift' => $this->boolean('is_night_shift'),
        ]);
    }
}
