<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => AssetCategory::factory(),
            'asset_tag' => 'AST-'.strtoupper(fake()->unique()->bothify('??-####')),
            'name' => fake()->words(3, true),
            'serial_number' => strtoupper(fake()->bothify('SN-########')),
            'purchase_cost' => fake()->randomFloat(2, 1000000, 25000000),
            'purchase_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'condition' => 'EXCELLENT',
            'status' => 'AVAILABLE',
        ];
    }

    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ASSIGNED',
        ]);
    }

    public function underMaintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'UNDER_MAINTENANCE',
            'condition' => 'DAMAGED',
        ]);
    }

    public function disposed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'DISPOSED',
            'condition' => 'DAMAGED',
        ]);
    }
}
