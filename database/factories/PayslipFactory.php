<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PayrollBatch;
use App\Models\Payslip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payslip>
 */
class PayslipFactory extends Factory
{
    protected $model = Payslip::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $basic = fake()->randomFloat(2, 4000000, 15000000);
        $allowances = fake()->randomFloat(2, 500000, 2000000);
        $overtime = fake()->randomFloat(2, 0, 1000000);
        $deductions = fake()->randomFloat(2, 200000, 800000);
        $net = $basic + $allowances + $overtime - $deductions;

        return [
            'payroll_batch_id' => PayrollBatch::factory(),
            'employee_id' => Employee::factory(),
            'slip_number' => 'SLIP-'.now()->format('Ym').'-'.strtoupper(fake()->unique()->bothify('######')),
            'basic_salary' => $basic,
            'total_allowances' => $allowances,
            'total_overtime_pay' => $overtime,
            'total_deductions' => $deductions,
            'net_salary' => $net,
            'bank_account_no' => fake()->bankAccountNumber(),
            'is_sent_email' => false,
            'sent_at' => null,
        ];
    }
}
