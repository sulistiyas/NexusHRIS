<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
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
        $employee = $this->route('employee');
        $userId = $employee->user_id ?? null;
        $employeeId = $employee->id ?? null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'role' => ['nullable', 'string', 'in:employee,manager,hr_admin,super_admin'],

            'employee_code' => ['required', 'string', 'max:50', Rule::unique('employees', 'employee_code')->ignore($employeeId)],
            'nik_ktp' => ['required', 'string', 'digits:16', Rule::unique('employees', 'nik_ktp')->ignore($employeeId)],
            'npwp' => ['nullable', 'string', 'max:50'],
            'bpjs_tk' => ['nullable', 'string', 'max:50'],
            'bpjs_kes' => ['nullable', 'string', 'max:50'],

            'branch_id' => ['required', 'exists:branches,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'designation_id' => ['required', 'exists:designations,id'],
            'manager_id' => ['nullable', 'exists:employees,id', Rule::notIn([$employeeId])],

            'gender' => ['nullable', 'in:MALE,FEMALE'],
            'birth_date' => ['nullable', 'date'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],

            'employment_status' => ['required', 'in:PKWT,PKWTT,INTERN,PROBATION'],
            'join_date' => ['required', 'date'],
            'contract_end_date' => ['nullable', 'date', 'after_or_equal:join_date'],

            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_no' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'manager_id.not_in' => 'Karyawan tidak dapat menjadi manajer bagi dirinya sendiri.',
            'nik_ktp.digits' => 'NIK KTP harus terdiri dari tepat 16 digit angka.',
        ];
    }
}
