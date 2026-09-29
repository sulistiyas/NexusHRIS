<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Attendance\ClockInRequest;
use App\Http\Requests\Attendance\ClockOutRequest;
use App\Http\Resources\V1\AttendanceResource;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use App\Services\AttendanceCalculationService;
use App\Services\GeoLocationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttendanceApiController extends BaseApiController
{
    public function __construct(
        protected GeoLocationService $geoService,
        protected AttendanceCalculationService $calcService
    ) {}

    /**
     * Cek status presensi karyawan hari ini.
     */
    public function today(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return $this->sendError('Profil karyawan tidak ditemukan.', null, 404);
        }

        $today = Carbon::today();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        return $this->sendSuccess(
            $attendance ? new AttendanceResource($attendance) : null,
            'Status presensi hari ini berhasil dimuat.'
        );
    }

    /**
     * Clock-In presensi melalui Mobile API.
     */
    public function clockIn(ClockInRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        $today = Carbon::today();
        $now = Carbon::now();

        // 1. Cek apakah sudah clock-in
        $existing = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing && $existing->clock_in) {
            return $this->sendError('Anda sudah melakukan Clock-In untuk hari ini.', null, 422);
        }

        // 2. Validasi Geofencing untuk WFO
        $workType = $request->input('work_type');
        $lat = (float) $request->input('latitude');
        $lon = (float) $request->input('longitude');

        if ($workType === 'WFO' && $employee->branch && $employee->branch->latitude && $employee->branch->longitude) {
            $branch = $employee->branch;
            $distance = $this->geoService->calculateDistance($lat, $lon, (float) $branch->latitude, (float) $branch->longitude);

            if ($distance > (float) $branch->radius_meters) {
                return $this->sendError("Anda berada di luar radius kantor ({$distance} m dari batas {$branch->radius_meters} m).", null, 422);
            }
        }

        // 3. Simpan foto selfie
        $selfieInput = $request->file('selfie_image') ?? $request->file('selfie') ?? $request->input('selfie') ?? $request->input('selfie_image');
        $selfiePath = $this->saveSelfie($selfieInput, $employee->id, 'in');

        // 4. Hitung Shift & Keterlambatan
        $shift = $this->resolveShift($employee, $today);
        $calc = $this->calcService->calculateClockIn($now, $shift);

        // 5. Simpan absensi
        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $today->toDateString()],
            [
                'shift_id' => $shift?->id,
                'clock_in' => $now->format('H:i:s'),
                'in_latitude' => $lat,
                'in_longitude' => $lon,
                'in_selfie_path' => $selfiePath,
                'work_type' => $workType,
                'status' => $calc['status'],
                'late_minutes' => $calc['late_minutes'],
                'notes' => $request->input('notes'),
            ]
        );

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'attendance_clock_in_api',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'attendance_id' => $attendance->id,
                'status' => $calc['status'],
                'late_minutes' => $calc['late_minutes'],
            ],
        ]);

        $message = $calc['status'] === 'LATE'
            ? "Clock-In berhasil dicatat (Terlambat {$calc['late_minutes']} menit)."
            : 'Clock-In berhasil dicatat tepat waktu!';

        return $this->sendSuccess(new AttendanceResource($attendance), $message, 201);
    }

    /**
     * Clock-Out presensi melalui Mobile API.
     */
    public function clockOut(ClockOutRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        $today = Carbon::today();
        $now = Carbon::now();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if (! $attendance || ! $attendance->clock_in) {
            return $this->sendError('Anda belum melakukan Clock-In hari ini.', null, 422);
        }

        if ($attendance->clock_out) {
            return $this->sendError('Anda sudah melakukan Clock-Out untuk hari ini.', null, 422);
        }

        // Validasi Geofencing untuk WFO
        $lat = (float) $request->input('latitude');
        $lon = (float) $request->input('longitude');

        if ($attendance->work_type === 'WFO' && $employee->branch && $employee->branch->latitude && $employee->branch->longitude) {
            $branch = $employee->branch;
            $distance = $this->geoService->calculateDistance($lat, $lon, (float) $branch->latitude, (float) $branch->longitude);

            if ($distance > (float) $branch->radius_meters) {
                return $this->sendError("Anda berada di luar radius kantor ({$distance} m dari batas {$branch->radius_meters} m).", null, 422);
            }
        }

        // Simpan foto selfie keluar
        $selfieInput = $request->file('selfie_image') ?? $request->file('selfie') ?? $request->input('selfie') ?? $request->input('selfie_image');
        $selfiePath = $this->saveSelfie($selfieInput, $employee->id, 'out');

        // Hitung Early Leave
        $shift = $attendance->shift ?? $this->resolveShift($employee, $today);
        $earlyLeave = $this->calcService->calculateClockOut($now, $shift);

        $attendance->update([
            'clock_out' => $now->format('H:i:s'),
            'out_latitude' => $lat,
            'out_longitude' => $lon,
            'out_selfie_path' => $selfiePath,
            'early_leave_minutes' => $earlyLeave['early_leave_minutes'],
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'attendance_clock_out_api',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'attendance_id' => $attendance->id,
                'early_leave_minutes' => $earlyLeave['early_leave_minutes'],
            ],
        ]);

        return $this->sendSuccess(new AttendanceResource($attendance), 'Clock-Out berhasil dicatat.');
    }

    /**
     * Riwayat absensi bulanan.
     */
    public function history(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return $this->sendError('Profil karyawan tidak ditemukan.', null, 404);
        }

        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);

        $attendances = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderByDesc('date')
            ->get();

        return $this->sendSuccess(
            AttendanceResource::collection($attendances),
            'Riwayat presensi berhasil dimuat.'
        );
    }

    protected function resolveShift(Employee $employee, Carbon $date): ?Shift
    {
        $scheduled = $employee->employeeShifts()
            ->where('date', $date->toDateString())
            ->with('shift')
            ->first();

        return $scheduled?->shift ?? Shift::first();
    }

    protected function saveSelfie(mixed $selfieInput, int $employeeId, string $type): string
    {
        $dir = 'attendances/selfies';
        $filename = "{$employeeId}_{$type}_".now()->format('Ymd_His').'.jpg';

        if ($selfieInput instanceof UploadedFile) {
            return $selfieInput->storeAs($dir, $filename, 'public');
        }

        if (is_string($selfieInput) && str_contains($selfieInput, 'base64,')) {
            $data = explode('base64,', $selfieInput)[1] ?? $selfieInput;
            $decoded = base64_decode($data);
            $path = "{$dir}/{$filename}";
            Storage::disk('public')->put($path, $decoded);

            return $path;
        }

        return '';
    }
}
