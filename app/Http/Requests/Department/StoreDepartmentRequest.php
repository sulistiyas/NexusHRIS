<?php

namespace App\Http\Requests\Department;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
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
            'branch_id' => ['required', 'exists:branches,id'],
            'code' => ['required', 'string', 'max:50', 'unique:departments,code'],
            'name' => ['required', 'string', 'max:100'],
            'manager_id' => ['nullable', 'exists:employees,id'],
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
            'branch_id.required' => 'Cabang kantor wajib dipilih.',
            'branch_id.exists' => 'Cabang kantor yang dipilih tidak valid.',
            'code.required' => 'Kode departemen wajib diisi.',
            'code.unique' => 'Kode departemen sudah digunakan.',
            'name.required' => 'Nama departemen wajib diisi.',
            'manager_id.exists' => 'Karyawan yang dipilih sebagai manajer tidak valid.',
        ];
    }
}
