<?php

namespace App\Services\Payroll;

class Pph21CalculatorService
{
    /**
     * Tentukan Kategori TER (A, B, atau C) berdasarkan status PTKP.
     * Mengacu pada PP 58/2023 & PMK 168/2023:
     * - Kategori A: TK/0, TK/1, K/0
     * - Kategori B: TK/2, TK/3, K/1, K/2
     * - Kategori C: K/3
     */
    public function getCategoryFromPtkp(string $ptkp): string
    {
        $normalized = strtoupper(trim(str_replace([' ', '-'], '', $ptkp)));

        return match ($normalized) {
            'TK/0', 'TK0', 'TK/1', 'TK1', 'K/0', 'K0' => 'A',
            'TK/2', 'TK2', 'TK/3', 'TK3', 'K/1', 'K1', 'K/2', 'K2' => 'B',
            'K/3', 'K3' => 'C',
            default => 'A', // Default standar ke Kategori A (TK/0)
        };
    }

    /**
     * Dapatkan tarif efektif persentase (TER) berdasarkan penghasilan bruto dan kategori.
     */
    public function getRate(float $grossIncome, string $category = 'A'): float
    {
        if ($grossIncome <= 0) {
            return 0.0;
        }

        $upperCategory = strtoupper(trim($category));
        if (strlen($upperCategory) > 1) {
            $upperCategory = $this->getCategoryFromPtkp($upperCategory);
        }

        return match ($upperCategory) {
            'B' => $this->getRateCategoryB($grossIncome),
            'C' => $this->getRateCategoryC($grossIncome),
            default => $this->getRateCategoryA($grossIncome),
        };
    }

    /**
     * Hitung pajak PPh 21 bulanan berdasarkan penghasilan bruto dan kategori PTKP.
     *
     * @return array{category: string, rate: float, gross_income: float, tax_amount: float}
     */
    public function calculate(float $grossIncome, string $ptkpOrCategory = 'A'): array
    {
        $category = strlen($ptkpOrCategory) === 1
            ? strtoupper($ptkpOrCategory)
            : $this->getCategoryFromPtkp($ptkpOrCategory);

        $rate = $this->getRate($grossIncome, $category);
        $taxAmount = round($grossIncome * $rate, 2);

        return [
            'category' => $category,
            'rate' => $rate,
            'gross_income' => round($grossIncome, 2),
            'tax_amount' => $taxAmount,
        ];
    }

    /**
     * Tabel Tarif TER Kategori A (PMK 168/2023).
     */
    protected function getRateCategoryA(float $income): float
    {
        return match (true) {
            $income <= 5400000 => 0.0,
            $income <= 5650000 => 0.0025,
            $income <= 5950000 => 0.005,
            $income <= 6300000 => 0.0075,
            $income <= 6750000 => 0.01,
            $income <= 7500000 => 0.0125,
            $income <= 8550000 => 0.015,
            $income <= 9650000 => 0.0175,
            $income <= 10050000 => 0.02,
            $income <= 10350000 => 0.0225,
            $income <= 10700000 => 0.025,
            $income <= 12500000 => 0.03,
            $income <= 13750000 => 0.04,
            $income <= 15100000 => 0.05,
            $income <= 16950000 => 0.06,
            $income <= 19750000 => 0.07,
            $income <= 24150000 => 0.08,
            $income <= 26450000 => 0.09,
            $income <= 28000000 => 0.10,
            $income <= 30050000 => 0.11,
            $income <= 32400000 => 0.12,
            $income <= 35400000 => 0.13,
            $income <= 39100000 => 0.14,
            $income <= 43850000 => 0.15,
            $income <= 47800000 => 0.16,
            $income <= 51400000 => 0.17,
            $income <= 56300000 => 0.18,
            $income <= 62200000 => 0.19,
            $income <= 68600000 => 0.20,
            $income <= 77500000 => 0.21,
            $income <= 89000000 => 0.22,
            $income <= 101900000 => 0.23,
            $income <= 120000000 => 0.24,
            $income <= 149000000 => 0.25,
            $income <= 191000000 => 0.26,
            $income <= 246000000 => 0.27,
            $income <= 326000000 => 0.28,
            $income <= 441000000 => 0.29,
            $income <= 578000000 => 0.30,
            $income <= 715000000 => 0.31,
            $income <= 922000000 => 0.32,
            $income <= 1400000000 => 0.33,
            default => 0.34,
        };
    }

    /**
     * Tabel Tarif TER Kategori B (PMK 168/2023).
     */
    protected function getRateCategoryB(float $income): float
    {
        return match (true) {
            $income <= 6200000 => 0.0,
            $income <= 6500000 => 0.0025,
            $income <= 6850000 => 0.005,
            $income <= 7300000 => 0.0075,
            $income <= 9200000 => 0.01,
            $income <= 10750000 => 0.015,
            $income <= 11250000 => 0.02,
            $income <= 11600000 => 0.025,
            $income <= 12600000 => 0.03,
            $income <= 13600000 => 0.04,
            $income <= 14950000 => 0.05,
            $income <= 16400000 => 0.06,
            $income <= 18450000 => 0.07,
            $income <= 21850000 => 0.08,
            $income <= 26000000 => 0.09,
            $income <= 27700000 => 0.10,
            $income <= 29350000 => 0.11,
            $income <= 31450000 => 0.12,
            $income <= 33950000 => 0.13,
            $income <= 37100000 => 0.14,
            $income <= 41100000 => 0.15,
            $income <= 45800000 => 0.16,
            $income <= 49500000 => 0.17,
            $income <= 53800000 => 0.18,
            $income <= 58500000 => 0.19,
            $income <= 64000000 => 0.20,
            $income <= 71000000 => 0.21,
            $income <= 80000000 => 0.22,
            $income <= 93000000 => 0.23,
            $income <= 109000000 => 0.24,
            $income <= 129000000 => 0.25,
            $income <= 163000000 => 0.26,
            $income <= 211000000 => 0.27,
            $income <= 274000000 => 0.28,
            $income <= 363000000 => 0.29,
            $income <= 486000000 => 0.30,
            $income <= 614000000 => 0.31,
            $income <= 786000000 => 0.32,
            $income <= 1189000000 => 0.33,
            default => 0.34,
        };
    }

    /**
     * Tabel Tarif TER Kategori C (PMK 168/2023).
     */
    protected function getRateCategoryC(float $income): float
    {
        return match (true) {
            $income <= 6600000 => 0.0,
            $income <= 6950000 => 0.0025,
            $income <= 7350000 => 0.005,
            $income <= 7800000 => 0.0075,
            $income <= 8850000 => 0.01,
            $income <= 9800000 => 0.0125,
            $income <= 10950000 => 0.015,
            $income <= 11200000 => 0.0175,
            $income <= 12050000 => 0.02,
            $income <= 12950000 => 0.03,
            $income <= 14150000 => 0.04,
            $income <= 15550000 => 0.05,
            $income <= 17050000 => 0.06,
            $income <= 19500000 => 0.07,
            $income <= 22700000 => 0.08,
            $income <= 24700000 => 0.09,
            $income <= 26350000 => 0.10,
            $income <= 28000000 => 0.11,
            $income <= 30050000 => 0.12,
            $income <= 32400000 => 0.13,
            $income <= 35400000 => 0.14,
            $income <= 39100000 => 0.15,
            $income <= 43850000 => 0.16,
            $income <= 47800000 => 0.17,
            $income <= 51400000 => 0.175,
            $income <= 56300000 => 0.18,
            $income <= 62200000 => 0.19,
            $income <= 68600000 => 0.20,
            $income <= 77500000 => 0.21,
            $income <= 89000000 => 0.22,
            $income <= 101900000 => 0.23,
            $income <= 120000000 => 0.24,
            $income <= 149000000 => 0.25,
            $income <= 191000000 => 0.26,
            $income <= 246000000 => 0.27,
            $income <= 326000000 => 0.28,
            $income <= 441000000 => 0.29,
            $income <= 578000000 => 0.30,
            $income <= 715000000 => 0.31,
            $income <= 922000000 => 0.32,
            $income <= 1400000000 => 0.33,
            default => 0.34,
        };
    }
}
