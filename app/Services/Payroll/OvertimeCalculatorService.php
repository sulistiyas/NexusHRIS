<?php

namespace App\Services\Payroll;

use App\Models\Overtime;
use Illuminate\Support\Collection;

class OvertimeCalculatorService
{
    /**
     * Hitung dasar upah lembur per jam sesuai PP 35/2021 (Pasal 32).
     * Rumus: 1/173 x (Gaji Pokok + Tunjangan Tetap)
     */
    public function calculateHourlyRate(float $basicSalary, float $fixedAllowance = 0): float
    {
        $monthlyBase = $basicSalary + $fixedAllowance;

        if ($monthlyBase <= 0) {
            return 0.0;
        }

        return round($monthlyBase / 173, 2);
    }

    /**
     * Hitung upah lembur untuk hari kerja biasa (Workday Overtime).
     * Sesuai PP 35/2021:
     * - Jam pertama: 1.5 x Upah Sejam
     * - Jam kedua dan seterusnya: 2.0 x Upah Sejam
     */
    public function calculateWorkdayOvertimePay(float $hourlyRate, float $hours): float
    {
        if ($hours <= 0 || $hourlyRate <= 0) {
            return 0.0;
        }

        if ($hours <= 1.0) {
            return round($hours * 1.5 * $hourlyRate, 2);
        }

        $firstHourPay = 1.0 * 1.5 * $hourlyRate;
        $remainingHoursPay = ($hours - 1.0) * 2.0 * $hourlyRate;

        return round($firstHourPay + $remainingHoursPay, 2);
    }

    /**
     * Kalkulasi total jam lembur yang disetujui (APPROVED) dan total upah lembur karyawan.
     *
     * @param  Collection<int, Overtime>|array<int, Overtime>  $overtimes
     * @return array{total_hours: float, total_pay: float, hourly_rate: float}
     */
    public function calculateTotalApprovedOvertime(Collection|array $overtimes, float $basicSalary, float $fixedAllowance = 0): array
    {
        $hourlyRate = $this->calculateHourlyRate($basicSalary, $fixedAllowance);
        $totalHours = 0.0;
        $totalPay = 0.0;

        foreach ($overtimes as $ot) {
            // Hanya kalkulasi data lembur yang sudah APPROVED
            if ($ot->status === 'APPROVED' && (float) $ot->total_hours > 0) {
                $hours = (float) $ot->total_hours;
                $totalHours += $hours;
                $totalPay += $this->calculateWorkdayOvertimePay($hourlyRate, $hours);
            }
        }

        return [
            'total_hours' => round($totalHours, 2),
            'total_pay' => round($totalPay, 2),
            'hourly_rate' => $hourlyRate,
        ];
    }
}
