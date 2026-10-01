<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetAssignment>
 */
class AssetAssignmentFactory extends Factory
{
    protected $model = AssetAssignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory()->assigned(),
            'employee_id' => Employee::factory(),
            'assigned_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'returned_date' => null,
            'return_condition' => null,
            'notes' => fake()->sentence(),
        ];
    }

    public function returned(?string $condition = 'GOOD'): static
    {
        return $this->state(fn (array $attributes) => [
            'returned_date' => now()->format('Y-m-d'),
            'return_condition' => $condition,
        ]);
    }
}
