<?php

namespace App\Actions\Employee;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateEmployeeAction
{
    public function __construct(
        protected GenerateEmployeeCodeAction $generateCodeAction
    ) {}

    /**
     * Jalankan transaksi penyimpanan karyawan dan pembuatan akun user.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data, ?User $actor = null): Employee
    {
        return DB::transaction(function () use ($data, $actor) {
            // 1. Generate NIP otomatis jika tidak diisi manual
            $employeeCode = ! empty($data['employee_code'])
                ? $data['employee_code']
                : $this->generateCodeAction->execute();

            // 2. Tentukan password akun (default: password atau acak)
            $rawPassword = $data['temporary_password'] ?? 'password';

            // 3. Buat entri tabel users
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($rawPassword),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // 4. Pasangkan Role Spatie (default: employee)
            $roleName = $data['role'] ?? 'employee';
            $user->syncRoles([$roleName]);

            // 5. Buat entri tabel employees
            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_code' => $employeeCode,
                'nik_ktp' => $data['nik_ktp'],
                'npwp' => $data['npwp'] ?? null,
                'bpjs_tk' => $data['bpjs_tk'] ?? null,
                'bpjs_kes' => $data['bpjs_kes'] ?? null,
                'branch_id' => $data['branch_id'],
                'department_id' => $data['department_id'],
                'designation_id' => $data['designation_id'],
                'manager_id' => $data['manager_id'] ?? null,
                'gender' => $data['gender'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'birth_place' => $data['birth_place'] ?? null,
                'phone' => $data['phone'] ?? null,
                'employment_status' => $data['employment_status'],
                'join_date' => $data['join_date'],
                'contract_end_date' => $data['contract_end_date'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_no' => $data['bank_account_no'] ?? null,
                'bank_account_holder' => $data['bank_account_holder'] ?? null,
            ]);

            // 6. Rekam riwayat di activity_logs
            if ($actor) {
                ActivityLog::create([
                    'user_id' => $actor->id,
                    'event' => 'employee_created',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'new_values' => [
                        'employee_id' => $employee->id,
                        'employee_code' => $employee->employee_code,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ]);
            }

            return $employee;
        });
    }
}
