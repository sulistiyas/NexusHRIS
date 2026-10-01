<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PayslipController extends Controller
{
    /**
     * Unduh atau cetak dokumen Slip Gaji PDF resmi.
     */
    public function download(Request $request, Payslip $payslip): Response
    {
        $user = $request->user();

        // Karyawan hanya boleh mengunduh slip gajinya sendiri yang sudah disetujui/dibayarkan
        if ($user->hasRole('employee') && ! $user->hasAnyRole(['super_admin', 'hr_admin'])) {
            if ($payslip->employee_id !== $user->employee?->id) {
                abort(403, 'Akses tidak sah.');
            }
            if (! in_array($payslip->payrollBatch->status, ['APPROVED', 'PAID'])) {
                abort(403, 'Slip gaji periode ini belum disetujui.');
            }
        }

        $payslip->load(['employee.user', 'employee.department', 'employee.designation', 'employee.branch', 'payrollBatch', 'items']);

        // Generate QR code link verifikasi keabsahan dokumen
        $verifyUrl = route('payslips.verify', $payslip->slip_number);
        $qrCodeSvg = base64_encode(QrCode::format('svg')->size(90)->errorCorrection('H')->generate($verifyUrl));

        $pdf = Pdf::loadView('payslips.pdf', compact('payslip', 'qrCodeSvg'))
            ->setPaper('a4', 'portrait');

        $fileName = "Slip_Gaji_{$payslip->slip_number}_{$payslip->employee->employee_code}.pdf";

        return $pdf->download($fileName);
    }

    /**
     * Halaman publik untuk memverifikasi keaslian dokumen dari scan QR Code.
     */
    public function verify(string $slipNumber): View
    {
        $payslip = Payslip::where('slip_number', $slipNumber)
            ->with(['employee.user', 'employee.designation', 'employee.department', 'payrollBatch'])
            ->first();

        return view('payslips.verify', compact('payslip', 'slipNumber'));
    }
}
