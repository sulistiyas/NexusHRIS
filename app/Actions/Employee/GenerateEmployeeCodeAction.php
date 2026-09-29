<?php

namespace App\Actions\Employee;

use App\Models\Employee;
use Carbon\Carbon;

class GenerateEmployeeCodeAction
{
    /**
     * Generate nomor induk pegawai (NIP) otomatis yang berurutan per bulan.
     */
    public function execute(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $prefix = 'NX-'.$date->format('Ym').'-';

        // Cari increment tertinggi di bulan yang sama
        $latestEmployee = Employee::where('employee_code', 'like', "{$prefix}%")
            ->orderByDesc('employee_code')
            ->first();

        if (! $latestEmployee) {
            return $prefix.'0001';
        }

        $lastNumber = (int) substr($latestEmployee->employee_code, -4);
        $nextNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);

        return $prefix.$nextNumber;
    }
}
