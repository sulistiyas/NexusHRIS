<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reimbursement>
 */
class ReimbursementFactory extends Factory
{
    protected $model = Reimbursement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'category_id' => ReimbursementCategory::factory(),
            'claim_number' => 'REIMB-'.now()->format('Ymd').'-'.strtoupper(fake()->unique()->bothify('####')),
            'claim_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'total_amount' => fake()->randomFloat(2, 50000, 2000000),
            'description' => fake()->sentence(),
            'status' => 'PENDING',
            'approved_by' => null,
            'disbursed_at' => null,
        ];
    }

    public function approved(?Employee $approver = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'APPROVED',
            'approved_by' => $approver?->id ?? Employee::factory(),
        ]);
    }

    public function disbursed(?Employee $approver = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'DISBURSED',
            'approved_by' => $approver?->id ?? Employee::factory(),
            'disbursed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'REJECTED',
        ]);
    }
}
