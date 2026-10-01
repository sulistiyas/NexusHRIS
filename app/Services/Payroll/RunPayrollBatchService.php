<?php

namespace App\Services\Payroll;

use App\Models\CashAdvance;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\PayrollBatch;
use App\Models\Payslip;
use App\Models\PayslipItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RunPayrollBatchService
{
    public function __construct(
        protected BpjsCalculatorService $bpjsCalculator,
        protected Pph21CalculatorService $pph21Calculator,
        protected OvertimeCalculatorService $overtimeCalculator
    ) {}

    /**
     * Eksekusi perhitungan payroll bulanan massal untuk seluruh karyawan aktif.
     *
     * @param  array{month: int, year: int, cut_off_start: string, cut_off_end: string, payment_date: string}  $data
     */
    public function execute(array $data): PayrollBatch
    {
        return DB::transaction(function () use ($data) {
            $month = (int) $data['month'];
            $year = (int) $data['year'];
            $cutOffStart = Carbon::parse($data['cut_off_start'])->startOfDay();
            $cutOffEnd = Carbon::parse($data['cut_off_end'])->endOfDay();
            $paymentDate = Carbon::parse($data['payment_date'])->toDateString();

            // Buat nomor batch unik: BATCH-YYYYMM-XXXX
            $datePrefix = sprintf('%04d%02d', $year, $month);
            $seq = PayrollBatch::where('batch_number', 'like', "BATCH-{$datePrefix}-%")->count() + 1;
            $batchNumber = sprintf('BATCH-%s-%04d', $datePrefix, $seq);

            $batch = PayrollBatch::create([
                'batch_number' => $batchNumber,
                'month' => $month,
                'year' => $year,
                'cut_off_start' => $cutOffStart->toDateString(),
                'cut_off_end' => $cutOffEnd->toDateString(),
                'payment_date' => $paymentDate,
                'total_gross' => 0,
                'total_deductions' => 0,
                'total_net' => 0,
                'status' => 'GENERATED',
            ]);

            $totalBatchGross = 0.0;
            $totalBatchDeductions = 0.0;
            $totalBatchNet = 0.0;

            // Ambil semua karyawan aktif yang sudah memiliki struktur gaji
            $employees = Employee::has('salaryStructure')
                ->with(['salaryStructure', 'user'])
                ->get();

            $slipCounter = 1;

            foreach ($employees as $employee) {
                $salary = $employee->salaryStructure;

                $basicSalary = (float) $salary->basic_salary;
                $fixedAllowance = (float) $salary->fixed_allowance;
                $transportAllowance = (float) $salary->transport_allowance;
                $mealAllowance = (float) $salary->meal_allowance;
                $totalAllowances = $fixedAllowance + $transportAllowance + $mealAllowance;

                // 1. Tarik lembur APPROVED dalam periode cut-off
                $approvedOvertimes = Overtime::where('employee_id', $employee->id)
                    ->where('status', 'APPROVED')
                    ->whereBetween('date', [$cutOffStart->toDateString(), $cutOffEnd->toDateString()])
                    ->get();

                $overtimeCalc = $this->overtimeCalculator->calculateTotalApprovedOvertime(
                    $approvedOvertimes,
                    $basicSalary,
                    $fixedAllowance
                );
                $overtimePay = $overtimeCalc['total_pay'];

                // Total Bruto (Gross)
                $grossSalary = $basicSalary + $totalAllowances + $overtimePay;

                // 2. Kalkulasi Iuran BPJS Ketenagakerjaan & Kesehatan
                $bpjs = $this->bpjsCalculator->calculate($basicSalary, $fixedAllowance);
                $bpjsKesDeduction = $bpjs['employee']['bpjs_kes'];
                $bpjsTkDeduction = $bpjs['employee']['total_bpjs_tk'];

                // 3. Kalkulasi Pajak PPh 21 TER 2024
                $taxResult = $this->pph21Calculator->calculate($grossSalary, 'TK/0');
                $taxDeduction = $taxResult['tax_amount'];

                // 4. Tarik cicilan pinjaman kasbon aktif
                $activeCashAdvance = CashAdvance::where('employee_id', $employee->id)
                    ->where('status', 'ACTIVE')
                    ->where('remaining_amount', '>', 0)
                    ->first();

                $cashAdvanceDeduction = 0.0;
                if ($activeCashAdvance) {
                    $cashAdvanceDeduction = min(
                        (float) $activeCashAdvance->monthly_deduction,
                        (float) $activeCashAdvance->remaining_amount
                    );
                }

                // Total Potongan (Deductions) & Bersih (Net / THP)
                $employeeDeductions = $bpjsKesDeduction + $bpjsTkDeduction + $taxDeduction + $cashAdvanceDeduction;
                $netSalary = max(0.0, $grossSalary - $employeeDeductions);

                // Buat nomor slip gaji unik: SLIP-YYYYMM-XXXX
                $slipNumber = sprintf('SLIP-%s-%04d', $datePrefix, $slipCounter++);

                $payslip = Payslip::create([
                    'payroll_batch_id' => $batch->id,
                    'employee_id' => $employee->id,
                    'slip_number' => $slipNumber,
                    'basic_salary' => $basicSalary,
                    'total_allowances' => $totalAllowances,
                    'total_overtime_pay' => $overtimePay,
                    'total_deductions' => $employeeDeductions,
                    'net_salary' => $netSalary,
                    'bank_account_no' => $employee->bank_account_no,
                    'is_sent_email' => false,
                ]);

                // Simpan Rincian Komponen Pendapatan (EARNING)
                PayslipItem::create([
                    'payslip_id' => $payslip->id,
                    'component_name' => 'Gaji Pokok',
                    'component_type' => 'EARNING',
                    'amount' => $basicSalary,
                ]);

                if ($fixedAllowance > 0) {
                    PayslipItem::create([
                        'payslip_id' => $payslip->id,
                        'component_name' => 'Tunjangan Tetap',
                        'component_type' => 'EARNING',
                        'amount' => $fixedAllowance,
                    ]);
                }

                if ($transportAllowance > 0) {
                    PayslipItem::create([
                        'payslip_id' => $payslip->id,
                        'component_name' => 'Tunjangan Transportasi',
                        'component_type' => 'EARNING',
                        'amount' => $transportAllowance,
                    ]);
                }

                if ($mealAllowance > 0) {
                    PayslipItem::create([
                        'payslip_id' => $payslip->id,
                        'component_name' => 'Tunjangan Makan',
                        'component_type' => 'EARNING',
                        'amount' => $mealAllowance,
                    ]);
                }

                if ($overtimePay > 0) {
                    PayslipItem::create([
                        'payslip_id' => $payslip->id,
                        'component_name' => "Upah Lembur ({$overtimeCalc['total_hours']} Jam)",
                        'component_type' => 'EARNING',
                        'amount' => $overtimePay,
                    ]);
                }

                // Simpan Rincian Komponen Potongan (DEDUCTION)
                PayslipItem::create([
                    'payslip_id' => $payslip->id,
                    'component_name' => 'BPJS Kesehatan (1%)',
                    'component_type' => 'DEDUCTION',
                    'amount' => $bpjsKesDeduction,
                ]);

                PayslipItem::create([
                    'payslip_id' => $payslip->id,
                    'component_name' => 'BPJS Ketenagakerjaan (JHT 2% + JP 1%)',
                    'component_type' => 'DEDUCTION',
                    'amount' => $bpjsTkDeduction,
                ]);

                $taxPercentage = $taxResult['rate'] * 100;
                PayslipItem::create([
                    'payslip_id' => $payslip->id,
                    'component_name' => "PPh 21 TER ({$taxPercentage}%)",
                    'component_type' => 'DEDUCTION',
                    'amount' => $taxDeduction,
                ]);

                if ($cashAdvanceDeduction > 0) {
                    PayslipItem::create([
                        'payslip_id' => $payslip->id,
                        'component_name' => "Cicilan Kasbon ({$activeCashAdvance->request_number})",
                        'component_type' => 'DEDUCTION',
                        'amount' => $cashAdvanceDeduction,
                    ]);
                }

                $totalBatchGross += $grossSalary;
                $totalBatchDeductions += $employeeDeductions;
                $totalBatchNet += $netSalary;
            }

            // Update Total Rekapitulasi di PayrollBatch
            $batch->update([
                'total_gross' => $totalBatchGross,
                'total_deductions' => $totalBatchDeductions,
                'total_net' => $totalBatchNet,
            ]);

            return $batch;
        });
    }
}
