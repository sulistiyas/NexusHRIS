<?php

namespace Database\Factories;

use App\Models\CashAdvance;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashAdvance>
 */
class CashAdvanceFactory extends Factory
{
    protected $model = CashAdvance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomElement([1000000, 2000000, 3000000]);
        $months = fake()->randomElement([1, 2, 3]);

        return [
            'employee_id' => Employee::factory(),
            'request_number' => 'CA-'.now()->format('Ym').'-'.strtoupper(fake()->unique()->bothify('####')),
            'amount' => $amount,
            'reason' => fake()->sentence(),
            'installment_months' => $months,
            'monthly_deduction' => $amount / $months,
            'remaining_amount' => $amount,
            'status' => 'PENDING',
        ];
    }
}
