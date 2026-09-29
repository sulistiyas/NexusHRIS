<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'code' => 'DEPT-'.strtoupper(fake()->unique()->bothify('??###')),
            'name' => fake()->randomElement(['Teknologi Informasi', 'Sumber Daya Manusia', 'Keuangan & Akuntansi', 'Operasional', 'Pemasaran']),
            'manager_id' => null,
        ];
    }
}
