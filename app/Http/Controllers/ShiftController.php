<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shift\StoreShiftRequest;
use App\Http\Requests\Shift\UpdateShiftRequest;
use App\Models\ActivityLog;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    /**
     * Tampilkan daftar master shift kerja.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $shifts = Shift::query()
            ->when($search, function ($query, $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->withCount(['employeeShifts', 'attendances'])
            ->orderBy('start_time')
            ->paginate(10)
            ->withQueryString();

        return view('shifts.index', compact('shifts', 'search'));
    }

    /**
     * Tampilkan formulir tambah shift kerja.
     */
    public function create(): View
    {
        return view('shifts.create');
    }

    /**
     * Simpan shift kerja baru ke database.
     */
    public function store(StoreShiftRequest $request): RedirectResponse
    {
        $shift = Shift::create($request->validated());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'shift_created',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => $shift->only(['name', 'start_time', 'end_time', 'grace_period_minutes', 'is_night_shift']),
        ]);

        return redirect()->route('shifts.index')
            ->with('success', "Shift kerja {$shift->name} berhasil ditambahkan.");
    }

    /**
     * Tampilkan formulir ubah shift kerja.
     */
    public function edit(Shift $shift): View
    {
        return view('shifts.edit', compact('shift'));
    }

    /**
     * Perbarui data shift kerja di database.
     */
    public function update(UpdateShiftRequest $request, Shift $shift): RedirectResponse
    {
        $oldValues = $shift->only(['name', 'start_time', 'end_time', 'grace_period_minutes', 'is_night_shift']);

        $shift->update($request->validated());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'shift_updated',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $shift->only(['name', 'start_time', 'end_time', 'grace_period_minutes', 'is_night_shift']),
        ]);

        return redirect()->route('shifts.index')
            ->with('success', "Data shift {$shift->name} berhasil diperbarui.");
    }

    /**
     * Hapus shift kerja jika belum digunakan pada jadwal atau riwayat absensi.
     */
    public function destroy(Request $request, Shift $shift): RedirectResponse
    {
        if ($shift->employeeShifts()->exists()) {
            return back()->with('error', 'Shift tidak dapat dihapus karena masih digunakan pada jadwal kerja karyawan.');
        }

        if ($shift->attendances()->exists()) {
            return back()->with('error', 'Shift tidak dapat dihapus karena sudah memiliki riwayat catatan absensi.');
        }

        $shiftName = $shift->name;
        $shift->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'shift_deleted',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => ['name' => $shiftName],
        ]);

        return redirect()->route('shifts.index')
            ->with('success', "Shift {$shiftName} berhasil dihapus.");
    }
}
