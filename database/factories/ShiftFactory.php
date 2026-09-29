<?php

namespace Database\Factories;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shift>
 */
class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Shift Pagi Reguler',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'grace_period_minutes' => 15,
            'is_night_shift' => false,
        ];
    }

    /**
     * State untuk shift malam.
     */
    public function nightShift(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Shift Malam',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'grace_period_minutes' => 10,
            'is_night_shift' => true,
        ]);
    }
}
