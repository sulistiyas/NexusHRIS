<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'BR-'.strtoupper(fake()->unique()->bothify('??###')),
            'name' => 'Cabang '.fake()->city(),
            'address' => fake()->address(),
            'latitude' => fake()->latitude(-6.3, -6.1),
            'longitude' => fake()->longitude(106.7, 106.9),
            'radius_meters' => 50,
            'timezone' => 'Asia/Jakarta',
        ];
    }
}
