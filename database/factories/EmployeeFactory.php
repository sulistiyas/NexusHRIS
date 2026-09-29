<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'employee_code' => 'NX-'.now()->format('Ym').'-'.fake()->unique()->numerify('####'),
            'nik_ktp' => fake()->unique()->numerify('################'),
            'branch_id' => Branch::factory(),
            'department_id' => Department::factory(),
            'designation_id' => Designation::factory(),
            'manager_id' => null,
            'employment_status' => 'PKWT',
            'join_date' => now()->subMonths(6),
        ];
    }
}
