<?php

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveType>
 */
class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'code' => 'CT-'.strtoupper(fake()->unique()->bothify('??##')),
            'name' => 'Cuti Tahunan',
            'default_days_per_year' => 12,
            'is_deduct_annual' => true,
            'requires_attachment' => false,
        ];
    }

    public function sick(): static
    {
        return $this->state(fn () => [
            'code' => 'CS-01',
            'name' => 'Cuti Sakit',
            'default_days_per_year' => 0,
            'is_deduct_annual' => false,
            'requires_attachment' => true,
        ]);
    }
}
