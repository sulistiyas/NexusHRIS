<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'shift_id' => Shift::factory(),
            'date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
            'in_latitude' => -6.2088,
            'in_longitude' => 106.8456,
            'out_latitude' => -6.2088,
            'out_longitude' => 106.8456,
            'late_minutes' => 0,
            'early_leave_minutes' => 0,
            'work_type' => 'WFO',
            'status' => 'PRESENT',
            'notes' => null,
        ];
    }

    public function late(int $minutes = 15): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'LATE',
            'late_minutes' => $minutes,
            'clock_in' => '08:15:00',
        ]);
    }

    public function onLeave(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'LEAVE',
            'clock_in' => null,
            'clock_out' => null,
        ]);
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ABSENT',
            'clock_in' => null,
            'clock_out' => null,
        ]);
    }
}
