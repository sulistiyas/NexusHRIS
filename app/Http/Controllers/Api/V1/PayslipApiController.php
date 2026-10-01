<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\V1\PayslipResource;
use App\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PayslipApiController extends BaseApiController
{
    /**
     * Ambil riwayat slip gaji bulanan karyawan (hanya yang sudah APPROVED atau PAID).
     */
    public function index(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return $this->sendError('Profil karyawan tidak ditemukan.', null, 404);
        }

        $payslips = Payslip::where('employee_id', $employee->id)
            ->whereHas('payrollBatch', function ($q): void {
                $q->whereIn('status', ['APPROVED', 'PAID']);
            })
            ->with(['payrollBatch', 'items'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return $this->sendSuccess(
            PayslipResource::collection($payslips)->response()->getData(true),
            'Daftar riwayat slip gaji berhasil dimuat.'
        );
    }

    /**
     * Dapatkan rincian slip gaji tertentu.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return $this->sendError('Profil karyawan tidak ditemukan.', null, 404);
        }

        $payslip = Payslip::where('employee_id', $employee->id)
            ->whereHas('payrollBatch', function ($q): void {
                $q->whereIn('status', ['APPROVED', 'PAID']);
            })
            ->with(['payrollBatch', 'items', 'employee.user', 'employee.department', 'employee.designation', 'employee.branch'])
            ->find($id);

        if (! $payslip) {
            return $this->sendError('Dokumen slip gaji tidak ditemukan atau belum disetujui.', null, 404);
        }

        return $this->sendSuccess(
            new PayslipResource($payslip),
            'Detail slip gaji berhasil dimuat.'
        );
    }

    /**
     * Unduh file PDF Slip Gaji resmi via REST API.
     */
    public function download(Request $request, int $id): Response|JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return $this->sendError('Profil karyawan tidak ditemukan.', null, 404);
        }

        $payslip = Payslip::where('employee_id', $employee->id)
            ->whereHas('payrollBatch', function ($q): void {
                $q->whereIn('status', ['APPROVED', 'PAID']);
            })
            ->with(['payrollBatch', 'items', 'employee.user', 'employee.department', 'employee.designation', 'employee.branch'])
            ->find($id);

        if (! $payslip) {
            return $this->sendError('Dokumen slip gaji tidak ditemukan atau belum disetujui.', null, 404);
        }

        $verifyUrl = route('payslips.verify', $payslip->slip_number);
        $qrCodeSvg = base64_encode(QrCode::format('svg')->size(90)->errorCorrection('H')->generate($verifyUrl));

        $pdf = Pdf::loadView('payslips.pdf', compact('payslip', 'qrCodeSvg'))
            ->setPaper('a4', 'portrait');

        $fileName = "Slip_Gaji_{$payslip->slip_number}_{$employee->employee_code}.pdf";

        return $pdf->download($fileName);
    }
}
