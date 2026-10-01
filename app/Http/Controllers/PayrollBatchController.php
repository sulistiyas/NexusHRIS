<?php

namespace App\Http\Controllers;

use App\Http\Requests\RunPayrollBatchRequest;
use App\Models\ActivityLog;
use App\Models\CashAdvance;
use App\Models\PayrollBatch;
use App\Services\Payroll\RunPayrollBatchService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PayrollBatchController extends Controller
{
    public function __construct(
        protected RunPayrollBatchService $batchService
    ) {}

    /**
     * Tampilkan riwayat master batch penggajian bulanan.
     */
    public function index(Request $request): View
    {
        $year = (int) $request->query('year', Carbon::now()->year);

        $batches = PayrollBatch::where('year', $year)
            ->withCount('payslips')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate(12);

        return view('payroll-batches.index', compact('batches', 'year'));
    }

    /**
     * Tampilkan formulir inisiasi proses batch payroll baru.
     */
    public function create(): View
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Default rentang cut-off: tanggal 21 bulan lalu s.d. 20 bulan ini
        $defaultStart = Carbon::now()->subMonth()->setDay(21)->toDateString();
        $defaultEnd = Carbon::now()->setDay(20)->toDateString();
        $defaultPayDate = Carbon::now()->setDay(25)->toDateString();

        return view('payroll-batches.create', compact('currentMonth', 'currentYear', 'defaultStart', 'defaultEnd', 'defaultPayDate'));
    }

    /**
     * Jalankan eksekusi perhitungan batch massal.
     */
    public function store(RunPayrollBatchRequest $request): RedirectResponse
    {
        $batch = $this->batchService->execute($request->validated());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'payroll_batch_generated',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => ['batch_number' => $batch->batch_number, 'total_net' => $batch->total_net],
        ]);

        return redirect()->route('payroll-batches.show', $batch->id)
            ->with('success', "Batch Penggajian {$batch->batch_number} berhasil dikalkulasi!");
    }

    /**
     * Tampilkan detail rekapitulasi slip gaji dalam suatu batch.
     */
    public function show(PayrollBatch $payrollBatch): View
    {
        $payrollBatch->load(['payslips.employee.user', 'payslips.employee.department', 'payslips.items']);

        return view('payroll-batches.show', compact('payrollBatch'));
    }

    /**
     * Setujui batch penggajian (APPROVE).
     */
    public function approve(Request $request, PayrollBatch $payrollBatch): RedirectResponse
    {
        if ($payrollBatch->status !== 'GENERATED' && $payrollBatch->status !== 'DRAFT') {
            return back()->with('error', 'Batch ini tidak dapat disetujui karena statusnya sudah berubah.');
        }

        $payrollBatch->update(['status' => 'APPROVED']);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'payroll_batch_approved',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => ['batch_number' => $payrollBatch->batch_number],
        ]);

        return back()->with('success', "Batch Penggajian {$payrollBatch->batch_number} berhasil disetujui (APPROVED).");
    }

    /**
     * Tandai batch telah ditransfer/dibayarkan (PAID) dan autodebet sisa kasbon karyawan.
     */
    public function markAsPaid(Request $request, PayrollBatch $payrollBatch): RedirectResponse
    {
        if ($payrollBatch->status !== 'APPROVED') {
            return back()->with('error', 'Batch harus berstatus APPROVED sebelum dapat ditandai sebagai dibayarkan.');
        }

        DB::transaction(function () use ($payrollBatch) {
            // Potong saldo pinjaman kasbon aktif yang terdaftar di payslip items batch ini
            $payslips = $payrollBatch->payslips()->with('items')->get();

            foreach ($payslips as $slip) {
                foreach ($slip->items as $item) {
                    if (str_starts_with($item->component_name, 'Cicilan Kasbon')) {
                        // Ambil nomor pengajuan di dalam tanda kurung
                        preg_match('/\((\w+-\w+-\w+)\)/', $item->component_name, $matches);
                        if (! empty($matches[1])) {
                            $ca = CashAdvance::where('request_number', $matches[1])->first();
                            if ($ca) {
                                $newRemaining = max(0.0, (float) $ca->remaining_amount - (float) $item->amount);
                                $ca->update([
                                    'remaining_amount' => $newRemaining,
                                    'status' => $newRemaining <= 0 ? 'PAID_OFF' : 'ACTIVE',
                                ]);
                            }
                        }
                    }
                }
            }

            $payrollBatch->update(['status' => 'PAID']);
        });

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'payroll_batch_paid',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => ['batch_number' => $payrollBatch->batch_number],
        ]);

        return back()->with('success', "Batch {$payrollBatch->batch_number} ditandai LUNAS/DIBAYARKAN (PAID). Sisa kasbon telah dipotong otomatis.");
    }

    /**
     * Hapus batch jika masih DRAFT / GENERATED.
     */
    public function destroy(PayrollBatch $payrollBatch): RedirectResponse
    {
        if ($payrollBatch->status === 'PAID') {
            return back()->with('error', 'Batch yang sudah berstatus PAID tidak dapat dihapus.');
        }

        $batchNumber = $payrollBatch->batch_number;
        $payrollBatch->delete();

        return redirect()->route('payroll-batches.index')
            ->with('success', "Batch {$batchNumber} berhasil dihapus.");
    }
}
