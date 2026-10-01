<?php

use App\Http\Controllers\Api\V1\AssetApiController;
use App\Http\Controllers\Api\V1\AttendanceApiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BranchApiController;
use App\Http\Controllers\Api\V1\CashAdvanceApiController;
use App\Http\Controllers\Api\V1\DashboardApiController;
use App\Http\Controllers\Api\V1\LeaveApiController;
use App\Http\Controllers\Api\V1\OvertimeApiController;
use App\Http\Controllers\Api\V1\PayslipApiController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ReimbursementApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function (): void {
    // Rute Publik Auth
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Rute Terproteksi Token (auth:sanctum)
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Master Cabang (Mobile Geofencing & Sync)
        Route::get('/branches', [BranchApiController::class, 'index']);
        Route::get('/branches/{branch}', [BranchApiController::class, 'show']);

        // Portal Profil Mandiri Karyawan (ESS) - Mobile API (US-10)
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
        Route::get('/profile/emergency-contacts', [ProfileController::class, 'emergencyContacts']);
        Route::post('/profile/emergency-contacts', [ProfileController::class, 'storeEmergencyContact']);
        Route::delete('/profile/emergency-contacts/{id}', [ProfileController::class, 'destroyEmergencyContact']);

        // Sprint 3: Presensi Cerdas (Attendance - US-12 & US-13)
        Route::get('/attendance/today', [AttendanceApiController::class, 'today']);
        Route::post('/attendance/clock-in', [AttendanceApiController::class, 'clockIn']);
        Route::post('/attendance/clock-out', [AttendanceApiController::class, 'clockOut']);
        Route::get('/attendance/history', [AttendanceApiController::class, 'history']);

        // Sprint 3: Manajemen Cuti & Saldo (Leaves - US-14 & US-15)
        Route::get('/leaves/balances', [LeaveApiController::class, 'balances']);
        Route::get('/leaves', [LeaveApiController::class, 'index']);
        Route::post('/leaves', [LeaveApiController::class, 'store']);
        Route::get('/leaves/approvals', [LeaveApiController::class, 'approvals']);
        Route::post('/leaves/{id}/approve', [LeaveApiController::class, 'approve']);

        // Sprint 3: Surat Perintah Lembur (Overtime - US-16)
        Route::get('/overtimes', [OvertimeApiController::class, 'index']);
        Route::post('/overtimes', [OvertimeApiController::class, 'store']);
        Route::get('/overtimes/approvals', [OvertimeApiController::class, 'approvals']);
        Route::post('/overtimes/{id}/approve', [OvertimeApiController::class, 'approve']);

        // Sprint 4: Slip Gaji Digital & Unduh PDF (US-20 & US-21)
        Route::get('/payslips', [PayslipApiController::class, 'index']);
        Route::get('/payslips/{id}', [PayslipApiController::class, 'show']);
        Route::get('/payslips/{id}/download', [PayslipApiController::class, 'download'])->name('api.payslips.download');

        // Sprint 4: Pengajuan Kasbon / Pinjaman Karyawan (US-22)
        Route::get('/cash-advances', [CashAdvanceApiController::class, 'index']);
        Route::post('/cash-advances', [CashAdvanceApiController::class, 'store']);

        // Sprint 5: Klaim Biaya Operasional (Reimbursements - US-23 & US-24)
        Route::get('/reimbursements/categories', [ReimbursementApiController::class, 'categories']);
        Route::get('/reimbursements', [ReimbursementApiController::class, 'index']);
        Route::post('/reimbursements', [ReimbursementApiController::class, 'store']);
        Route::get('/reimbursements/{id}', [ReimbursementApiController::class, 'show']);

        // Sprint 5: Inventaris Aset Kantor & Serah Terima (Assets - US-25)
        Route::get('/assets/my-assets', [AssetApiController::class, 'myAssets']);
        Route::get('/assets', [AssetApiController::class, 'index']);

        // Sprint 5: Dashboard Ringkasan Eksekutif Mobile (US-26)
        Route::get('/dashboard/summary', [DashboardApiController::class, 'summary']);
    });
});
