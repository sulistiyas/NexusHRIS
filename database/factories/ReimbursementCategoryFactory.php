<?php

namespace Database\Factories;

use App\Models\ReimbursementCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReimbursementCategory>
 */
class ReimbursementCategoryFactory extends Factory
{
    protected $model = ReimbursementCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
        ];
    }
}
