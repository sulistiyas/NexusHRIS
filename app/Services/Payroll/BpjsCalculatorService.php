<?php

namespace App\Services\Payroll;

class BpjsCalculatorService
{
    /**
     * Plafon maksimal dasar upah untuk BPJS Kesehatan (Kepres / Perpres No. 64/2020).
     */
    public const BPJS_KES_MAX_BASE = 12000000.0;

    /**
     * Plafon maksimal dasar upah untuk BPJS Ketenagakerjaan - Jaminan Pensiun (JP 2024/2025).
     */
    public const BPJS_TK_JP_MAX_BASE = 10042300.0;

    /**
     * Tarif resmi BPJS Kesehatan.
     */
    public const RATE_KES_EMPLOYEE = 0.01; // 1%

    public const RATE_KES_EMPLOYER = 0.04; // 4%

    /**
     * Tarif resmi BPJS Ketenagakerjaan.
     */
    public const RATE_TK_JHT_EMPLOYEE = 0.02;   // 2%

    public const RATE_TK_JHT_EMPLOYER = 0.037;  // 3.7%

    public const RATE_TK_JP_EMPLOYEE = 0.01;    // 1%

    public const RATE_TK_JP_EMPLOYER = 0.02;    // 2%

    public const RATE_TK_JKK_EMPLOYER = 0.0024; // 0.24% (Tingkat Risiko Rendah / Kantor)

    public const RATE_TK_JKM_EMPLOYER = 0.0030; // 0.30%

    /**
     * Hitung rincian iuran BPJS Kesehatan dan BPJS Ketenagakerjaan.
     *
     * @param  float  $basicSalary  Gaji Pokok
     * @param  float  $fixedAllowance  Tunjangan Tetap
     * @return array{
     *     basis_wage: float,
     *     employee: array{
     *         bpjs_kes: float,
     *         jht: float,
     *         jp: float,
     *         total_bpjs_tk: float,
     *         total_deduction: float
     *     },
     *     employer: array{
     *         bpjs_kes: float,
     *         jht: float,
     *         jp: float,
     *         jkk: float,
     *         jkm: float,
     *         total_bpjs_tk: float,
     *         total_contribution: float
     *     },
     *     total_grand: float
     * }
     */
    public function calculate(float $basicSalary, float $fixedAllowance = 0): array
    {
        $basisWage = max(0.0, $basicSalary + $fixedAllowance);

        // Dasar pengali BPJS Kesehatan (dibatasi plafon)
        $kesBasis = min($basisWage, self::BPJS_KES_MAX_BASE);
        $kesEmployee = round($kesBasis * self::RATE_KES_EMPLOYEE, 2);
        $kesEmployer = round($kesBasis * self::RATE_KES_EMPLOYER, 2);

        // Dasar pengali BPJS TK - JHT (tanpa batas plafon)
        $jhtEmployee = round($basisWage * self::RATE_TK_JHT_EMPLOYEE, 2);
        $jhtEmployer = round($basisWage * self::RATE_TK_JHT_EMPLOYER, 2);

        // Dasar pengali BPJS TK - JP (dibatasi plafon)
        $jpBasis = min($basisWage, self::BPJS_TK_JP_MAX_BASE);
        $jpEmployee = round($jpBasis * self::RATE_TK_JP_EMPLOYEE, 2);
        $jpEmployer = round($jpBasis * self::RATE_TK_JP_EMPLOYER, 2);

        // Dasar pengali BPJS TK - JKK & JKM (ditanggung penuh pemberi kerja)
        $jkkEmployer = round($basisWage * self::RATE_TK_JKK_EMPLOYER, 2);
        $jkmEmployer = round($basisWage * self::RATE_TK_JKM_EMPLOYER, 2);

        $totalTkEmployee = round($jhtEmployee + $jpEmployee, 2);
        $totalTkEmployer = round($jhtEmployer + $jpEmployer + $jkkEmployer + $jkmEmployer, 2);

        $totalEmployeeDeduction = round($kesEmployee + $totalTkEmployee, 2);
        $totalEmployerContribution = round($kesEmployer + $totalTkEmployer, 2);

        return [
            'basis_wage' => $basisWage,
            'employee' => [
                'bpjs_kes' => $kesEmployee,
                'jht' => $jhtEmployee,
                'jp' => $jpEmployee,
                'total_bpjs_tk' => $totalTkEmployee,
                'total_deduction' => $totalEmployeeDeduction,
            ],
            'employer' => [
                'bpjs_kes' => $kesEmployer,
                'jht' => $jhtEmployer,
                'jp' => $jpEmployer,
                'jkk' => $jkkEmployer,
                'jkm' => $jkmEmployer,
                'total_bpjs_tk' => $totalTkEmployer,
                'total_contribution' => $totalEmployerContribution,
            ],
            'total_grand' => round($totalEmployeeDeduction + $totalEmployerContribution, 2),
        ];
    }
}
