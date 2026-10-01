<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCashAdvanceRequest;
use App\Models\ActivityLog;
use App\Models\CashAdvance;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EssCashAdvanceController extends Controller
{
    /**
     * Tampilkan riwayat permohonan kasbon karyawan yang sedang login.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return redirect()->route('dashboard')->with('error', 'Data profil karyawan Anda tidak ditemukan.');
        }

        $cashAdvances = CashAdvance::where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        // Hitung total sisa pinjaman aktif berjalan
        $activeDebt = CashAdvance::where('employee_id', $employee->id)
            ->where('status', 'ACTIVE')
            ->sum('remaining_amount');

        return view('ess.cash-advances.index', compact('employee', 'cashAdvances', 'activeDebt'));
    }

    /**
     * Tampilkan formulir pengajuan kasbon baru.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return redirect()->route('dashboard');
        }

        // Cek apakah ada pengajuan yang masih PENDING
        $hasPending = CashAdvance::where('employee_id', $employee->id)
            ->where('status', 'PENDING')
            ->exists();

        return view('ess.cash-advances.create', compact('employee', 'hasPending'));
    }

    /**
     * Simpan pengajuan kasbon baru ke database.
     */
    public function store(StoreCashAdvanceRequest $request): RedirectResponse
    {
        $employee = $request->user()->employee;

        // Cegah permohonan ganda jika masih ada pengajuan yang PENDING
        $hasPending = CashAdvance::where('employee_id', $employee->id)
            ->where('status', 'PENDING')
            ->exists();

        if ($hasPending) {
            return back()->with('error', 'Anda masih memiliki permohonan kasbon berstatus Menunggu Persetujuan (PENDING).');
        }

        $amount = (float) $request->validated('amount');
        $months = (int) $request->validated('installment_months');
        $monthlyDeduction = round($amount / $months, 2);

        // Buat nomor request unik berformat: CA-YYYYMM-XXXX
        $datePrefix = Carbon::now()->format('Ym');
        $seq = CashAdvance::where('request_number', 'like', "CA-{$datePrefix}-%")->count() + 1;
        $requestNumber = sprintf('CA-%s-%04d', $datePrefix, $seq);

        $cashAdvance = CashAdvance::create([
            'employee_id' => $employee->id,
            'request_number' => $requestNumber,
            'amount' => $amount,
            'reason' => $request->validated('reason'),
            'installment_months' => $months,
            'monthly_deduction' => $monthlyDeduction,
            'remaining_amount' => $amount,
            'status' => 'PENDING',
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'cash_advance_requested',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => ['request_number' => $requestNumber, 'amount' => $amount],
        ]);

        return redirect()->route('ess.cash-advances.index')
            ->with('success', "Permohonan kasbon {$requestNumber} berhasil diajukan dan sedang menunggu peninjauan.");
    }
}
