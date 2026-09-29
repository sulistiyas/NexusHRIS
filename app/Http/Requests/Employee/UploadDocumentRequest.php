<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['super_admin', 'hr_admin']) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', 'in:KTP,NPWP,IJAZAH,KONTRAK,SERTIFIKAT,LAINNYA'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // Max 5MB
            'expiry_date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'document_type.required' => 'Jenis dokumen wajib dipilih.',
            'file.required' => 'File dokumen wajib dipilih.',
            'file.mimes' => 'Format file yang diperbolehkan hanya PDF, JPG, JPEG, atau PNG.',
            'file.max' => 'Ukuran file dokumen maksimal 5MB.',
        ];
    }
}
