<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Overtime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Overtime>
 */
class OvertimeFactory extends Factory
{
    protected $model = Overtime::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'date' => fake()->date(),
            'start_time' => '17:00:00',
            'end_time' => '20:00:00',
            'total_hours' => 3.00,
            'reason' => 'Menyelesaikan laporan bulanan',
            'status' => 'PENDING',
        ];
    }

    public function approved(?Employee $approver = null): static
    {
        return $this->state(fn () => [
            'status' => 'APPROVED',
            'approved_by' => $approver?->id,
            'approved_at' => now(),
        ]);
    }
}
