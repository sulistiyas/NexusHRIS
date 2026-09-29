<?php

namespace App\Http\Controllers;

use App\Http\Requests\Overtime\StoreOvertimeRequest;
use App\Models\ActivityLog;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EssOvertimeController extends Controller
{
    /**
     * Tampilkan riwayat pengajuan lembur pribadi karyawan.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda tidak terhubung dengan data karyawan.');
        }

        $overtimes = Overtime::query()
            ->where('employee_id', $employee->id)
            ->with('approvedBy.user')
            ->orderByDesc('date')
            ->paginate(10);

        return view('ess.overtimes.index', compact('employee', 'overtimes'));
    }

    /**
     * Tampilkan formulir pengajuan surat perintah lembur.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return redirect()->route('dashboard');
        }

        return view('ess.overtimes.create', compact('employee'));
    }

    /**
     * Simpan pengajuan lembur baru.
     */
    public function store(StoreOvertimeRequest $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        $data = $request->validated();

        $start = Carbon::parse("{$data['date']} {$data['start_time']}");
        $end = Carbon::parse("{$data['date']} {$data['end_time']}");

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay(); // Lintas tengah malam
        }

        $minutes = $start->diffInMinutes($end);
        if ($minutes < 30) {
            throw ValidationException::withMessages([
                'end_time' => 'Durasi lembur minimal 30 menit.',
            ]);
        }

        $totalHours = round($minutes / 60, 2);

        $overtime = Overtime::create([
            'employee_id' => $employee->id,
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'total_hours' => $totalHours,
            'reason' => $data['reason'],
            'status' => 'PENDING',
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'overtime_requested',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'overtime_id' => $overtime->id,
                'total_hours' => $totalHours,
            ],
        ]);

        return redirect()->route('ess.overtimes.index')
            ->with('success', "Permohonan lembur selama {$totalHours} jam berhasil diajukan.");
    }
}
