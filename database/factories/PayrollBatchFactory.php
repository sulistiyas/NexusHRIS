<?php

namespace Database\Factories;

use App\Models\PayrollBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollBatch>
 */
class PayrollBatchFactory extends Factory
{
    protected $model = PayrollBatch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'batch_number' => 'BATCH-'.now()->format('Ym').'-'.strtoupper(fake()->unique()->bothify('####')),
            'month' => (int) now()->format('m'),
            'year' => (int) now()->format('Y'),
            'cut_off_start' => now()->startOfMonth()->format('Y-m-d'),
            'cut_off_end' => now()->endOfMonth()->format('Y-m-d'),
            'payment_date' => now()->format('Y-m-d'),
            'status' => 'PAID',
            'total_gross' => 50000000.00,
            'total_deductions' => 5000000.00,
            'total_net' => 45000000.00,
        ];
    }
}
