<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Designation>
 */
class DesignationFactory extends Factory
{
    protected $model = Designation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'title' => fake()->randomElement(['Software Engineer', 'HR Specialist', 'Financial Analyst', 'Operations Supervisor', 'Marketing Officer']),
            'grade_level' => fake()->randomElement(['Level 1 - Staff', 'Level 2 - Senior Staff', 'Level 3 - Supervisor', 'Level 4 - Manager']),
        ];
    }
}
