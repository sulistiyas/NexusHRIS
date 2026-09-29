<?php

namespace App\Http\Requests\Designation;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDesignationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['super_admin', 'hr_admin']) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'department_id' => ['required', 'exists:departments,id'],
            'title' => ['required', 'string', 'max:100'],
            'grade_level' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'department_id.required' => 'Departemen wajib dipilih.',
            'department_id.exists' => 'Departemen yang dipilih tidak valid.',
            'title.required' => 'Nama jabatan wajib diisi.',
            'title.max' => 'Nama jabatan maksimal 100 karakter.',
        ];
    }
}
