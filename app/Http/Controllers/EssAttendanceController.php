<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\ClockInRequest;
use App\Http\Requests\Attendance\ClockOutRequest;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use App\Services\AttendanceCalculationService;
use App\Services\GeoLocationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EssAttendanceController extends Controller
{
    public function __construct(
        protected GeoLocationService $geoService,
        protected AttendanceCalculationService $calcService
    ) {}

    /**
     * Tampilkan halaman presensi mandiri karyawan (ESS).
     */
    public function index(Request $request): View|RedirectResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda tidak terhubung dengan data karyawan.');
        }

        $employee->load('branch');
        $today = Carbon::today();

        // Cari riwayat presensi hari ini
        $attendanceToday = Attendance::query()
            ->where('employee_id', $employee->id)
            ->where('date', $today)
            ->with('shift')
            ->first();

        // Shift hari ini
        $shiftToday = $this->resolveShift($employee, $today);

        // Riwayat 10 presensi terakhir
        $recentAttendances = Attendance::query()
            ->where('employee_id', $employee->id)
            ->with('shift')
            ->orderByDesc('date')
            ->take(10)
            ->get();

        return view('ess.attendance', compact('employee', 'attendanceToday', 'shiftToday', 'recentAttendances'));
    }

    /**
     * Proses Clock-In.
     */
    public function clockIn(ClockInRequest $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        $today = Carbon::today();
        $now = Carbon::now();

        // 1. Cek apakah sudah clock-in
        $existing = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing && $existing->clock_in) {
            return back()->with('error', 'Anda sudah melakukan Clock-In untuk hari ini.');
        }

        // 2. Validasi Geofencing untuk WFO
        $workType = $request->input('work_type');
        $lat = (float) $request->input('latitude');
        $lon = (float) $request->input('longitude');

        if ($workType === 'WFO' && $employee->branch && $employee->branch->latitude && $employee->branch->longitude) {
            $branch = $employee->branch;
            $distance = $this->geoService->calculateDistance($lat, $lon, (float) $branch->latitude, (float) $branch->longitude);

            if ($distance > (float) $branch->radius_meters) {
                return back()->with('error', "Anda berada di luar radius kantor ({$distance} m dari batas {$branch->radius_meters} m).");
            }
        }

        // 3. Simpan foto selfie
        $selfiePath = $this->saveSelfie($request->input('selfie') ?? $request->file('selfie'), $employee->id, 'in');

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
            'event' => 'attendance_clock_in',
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

        return back()->with('success', $message);
    }

    /**
     * Proses Clock-Out.
     */
    public function clockOut(ClockOutRequest $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        $today = Carbon::today();
        $now = Carbon::now();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (! $attendance || ! $attendance->clock_in) {
            return back()->with('error', 'Anda belum melakukan Clock-In hari ini.');
        }

        if ($attendance->clock_out) {
            return back()->with('error', 'Anda sudah melakukan Clock-Out untuk hari ini.');
        }

        // Validasi Geofencing untuk WFO
        $lat = (float) $request->input('latitude');
        $lon = (float) $request->input('longitude');

        if ($attendance->work_type === 'WFO' && $employee->branch && $employee->branch->latitude && $employee->branch->longitude) {
            $branch = $employee->branch;
            $distance = $this->geoService->calculateDistance($lat, $lon, (float) $branch->latitude, (float) $branch->longitude);

            if ($distance > (float) $branch->radius_meters) {
                return back()->with('error', "Anda berada di luar radius kantor ({$distance} m dari batas {$branch->radius_meters} m).");
            }
        }

        // Simpan foto selfie keluar
        $selfiePath = $this->saveSelfie($request->input('selfie') ?? $request->file('selfie'), $employee->id, 'out');

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
            'event' => 'attendance_clock_out',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'attendance_id' => $attendance->id,
                'early_leave_minutes' => $earlyLeave['early_leave_minutes'],
            ],
        ]);

        return back()->with('success', 'Clock-Out berhasil dicatat. Terima kasih atas kerja keras Anda hari ini!');
    }

    /**
     * Dapatkan shift untuk karyawan pada hari tertentu.
     */
    protected function resolveShift(Employee $employee, Carbon $date): ?Shift
    {
        $scheduled = $employee->employeeShifts()
            ->where('date', $date->toDateString())
            ->with('shift')
            ->first();

        return $scheduled?->shift ?? Shift::first();
    }

    /**
     * Simpan selfie baik dari UploadedFile maupun Base64 string.
     */
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
