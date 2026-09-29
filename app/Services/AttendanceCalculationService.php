<?php

namespace App\Services;

use App\Models\Shift;
use Carbon\Carbon;

class AttendanceCalculationService
{
    /**
     * Hitung status kehadiran dan menit keterlambatan saat Clock-In.
     *
     * @return array{status: string, late_minutes: int}
     */
    public function calculateClockIn(Carbon $clockInTime, ?Shift $shift): array
    {
        if (! $shift) {
            return [
                'status' => 'PRESENT',
                'late_minutes' => 0,
            ];
        }

        $dateString = $clockInTime->toDateString();
        $officialStart = Carbon::parse("{$dateString} {$shift->start_time}");
        $graceLimit = (clone $officialStart)->addMinutes($shift->grace_period_minutes);

        if ($clockInTime->greaterThan($graceLimit)) {
            $lateMinutes = (int) $officialStart->diffInMinutes($clockInTime);

            return [
                'status' => 'LATE',
                'late_minutes' => $lateMinutes,
            ];
        }

        return [
            'status' => 'PRESENT',
            'late_minutes' => 0,
        ];
    }

    /**
     * Hitung menit pulang lebih awal saat Clock-Out.
     *
     * @return array{early_leave_minutes: int}
     */
    public function calculateClockOut(Carbon $clockOutTime, ?Shift $shift): array
    {
        if (! $shift) {
            return [
                'early_leave_minutes' => 0,
            ];
        }

        $dateString = $clockOutTime->toDateString();
        $officialEnd = Carbon::parse("{$dateString} {$shift->end_time}");

        // Jika shift malam (melewati tengah malam)
        if ($shift->is_night_shift && $shift->end_time < $shift->start_time) {
            $officialEnd->addDay();
        }

        if ($clockOutTime->lessThan($officialEnd)) {
            $earlyMinutes = (int) $clockOutTime->diffInMinutes($officialEnd);

            return [
                'early_leave_minutes' => $earlyMinutes,
            ];
        }

        return [
            'early_leave_minutes' => 0,
        ];
    }
}
