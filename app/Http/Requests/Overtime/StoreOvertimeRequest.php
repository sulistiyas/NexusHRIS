<?php

namespace App\Http\Requests\Overtime;

use Illuminate\Foundation\Http\FormRequest;

class StoreOvertimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->employee !== null;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal lembur wajib diisi.',
            'start_time.required' => 'Jam mulai lembur wajib diisi.',
            'start_time.date_format' => 'Format jam mulai harus HH:mm (contoh: 17:00).',
            'end_time.required' => 'Jam selesai lembur wajib diisi.',
            'end_time.date_format' => 'Format jam selesai harus HH:mm (contoh: 20:00).',
            'reason.required' => 'Alasan atau rincian pekerjaan lembur wajib diisi.',
        ];
    }
}
