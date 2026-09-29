<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'ANNUAL',
                'name' => 'Cuti Tahunan',
                'default_days_per_year' => 12,
                'is_deduct_annual' => true,
                'requires_attachment' => false,
            ],
            [
                'code' => 'SICK',
                'name' => 'Izin Sakit',
                'default_days_per_year' => 14,
                'is_deduct_annual' => false,
                'requires_attachment' => true,
            ],
            [
                'code' => 'MATERNITY',
                'name' => 'Cuti Melahirkan',
                'default_days_per_year' => 90,
                'is_deduct_annual' => false,
                'requires_attachment' => true,
            ],
            [
                'code' => 'SPECIAL',
                'name' => 'Izin Khusus / Menikah / Berduka',
                'default_days_per_year' => 3,
                'is_deduct_annual' => false,
                'requires_attachment' => false,
            ],
        ];

        foreach ($types as $type) {
            LeaveType::firstOrCreate(['code' => $type['code']], $type);
        }
    }
}
