<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EssPayslipController extends Controller
{
    /**
     * Tampilkan riwayat slip gaji bulanan karyawan login.
     * Hanya menampilkan slip yang status batch-nya sudah APPROVED atau PAID.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return redirect()->route('dashboard')->with('error', 'Data profil karyawan tidak ditemukan.');
        }

        $payslips = Payslip::where('employee_id', $employee->id)
            ->whereHas('payrollBatch', function ($q): void {
                $q->whereIn('status', ['APPROVED', 'PAID']);
            })
            ->with('payrollBatch')
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('ess.payslips.index', compact('employee', 'payslips'));
    }

    /**
     * Tampilkan rincian detail slip gaji karyawan tertentu di web.
     */
    public function show(Request $request, Payslip $payslip): View|RedirectResponse
    {
        $employee = $request->user()->employee;
        if (! $employee || $payslip->employee_id !== $employee->id) {
            abort(403, 'Akses tidak sah.');
        }

        if (! in_array($payslip->payrollBatch->status, ['APPROVED', 'PAID'])) {
            abort(403, 'Slip gaji periode ini belum disetujui.');
        }

        $payslip->load(['payrollBatch', 'items', 'employee.designation', 'employee.department', 'employee.branch']);

        $earnings = $payslip->items->where('component_type', 'EARNING');
        $deductions = $payslip->items->where('component_type', 'DEDUCTION');

        return view('ess.payslips.show', compact('employee', 'payslip', 'earnings', 'deductions'));
    }

    /**
     * Unduh file PDF resmi slip gaji karyawan.
     */
    public function download(Request $request, Payslip $payslip): Response
    {
        $employee = $request->user()->employee;
        if (! $employee || $payslip->employee_id !== $employee->id) {
            abort(403, 'Akses tidak sah.');
        }

        if (! in_array($payslip->payrollBatch->status, ['APPROVED', 'PAID'])) {
            abort(403, 'Slip gaji periode ini belum disetujui.');
        }

        $payslip->load(['employee.user', 'employee.department', 'employee.designation', 'employee.branch', 'payrollBatch', 'items']);

        $verifyUrl = route('payslips.verify', $payslip->slip_number);
        $qrCodeSvg = base64_encode(QrCode::format('svg')->size(90)->errorCorrection('H')->generate($verifyUrl));

        $pdf = Pdf::loadView('payslips.pdf', compact('payslip', 'qrCodeSvg'))
            ->setPaper('a4', 'portrait');

        $fileName = "Slip_Gaji_{$payslip->slip_number}_{$employee->employee_code}.pdf";

        return $pdf->download($fileName);
    }
}
