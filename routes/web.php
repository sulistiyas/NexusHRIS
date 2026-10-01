<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CashAdvanceApprovalController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\EssAttendanceController;
use App\Http\Controllers\EssCashAdvanceController;
use App\Http\Controllers\EssLeaveController;
use App\Http\Controllers\EssOvertimeController;
use App\Http\Controllers\EssPayslipController;
use App\Http\Controllers\EssProfileController;
use App\Http\Controllers\LeaveApprovalController;
use App\Http\Controllers\OvertimeApprovalController;
use App\Http\Controllers\PayrollBatchController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\SalaryStructureController;
use App\Http\Controllers\ShiftController;
use Illuminate\Support\Facades\Route;

// Rute Publik (Verifikasi Keaslian Slip Gaji dari Scan QR)
Route::get('/verify/payslip/{slipNumber}', [PayslipController::class, 'verify'])->name('payslips.verify');

// Rute Tamu (Guest)
Route::middleware('guest')->group(function (): void {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Rute Terautentikasi Umum (Auth)
Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Modul Berkas Dokumen Karyawan
    Route::post('/employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])->name('employees.documents.store');
    Route::get('/documents/{document}/download', [EmployeeDocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [EmployeeDocumentController::class, 'destroy'])->name('documents.destroy');
    // Modul Portal Profil Mandiri Karyawan (ESS)
    Route::prefix('ess')->name('ess.')->group(function (): void {
        Route::get('/profile', [EssProfileController::class, 'index'])->name('profile');
        Route::post('/profile', [EssProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/avatar', [EssProfileController::class, 'uploadAvatar'])->name('profile.avatar');
        Route::post('/emergency-contacts', [EssProfileController::class, 'storeEmergencyContact'])->name('emergency-contacts.store');
        Route::delete('/emergency-contacts/{id}', [EssProfileController::class, 'destroyEmergencyContact'])->name('emergency-contacts.destroy');
        Route::get('/attendance', [EssAttendanceController::class, 'index'])->name('attendance');
        Route::post('/attendance/clock-in', [EssAttendanceController::class, 'clockIn'])->name('attendance.clock-in');
        Route::post('/attendance/clock-out', [EssAttendanceController::class, 'clockOut'])->name('attendance.clock-out');
        Route::resource('leaves', EssLeaveController::class)->only(['index', 'create', 'store', 'show']);
        Route::resource('overtimes', EssOvertimeController::class)->only(['index', 'create', 'store']);
        Route::resource('cash-advances', EssCashAdvanceController::class)->only(['index', 'create', 'store']);
        Route::get('/payslips', [EssPayslipController::class, 'index'])->name('payslips.index');
        Route::get('/payslips/{payslip}', [EssPayslipController::class, 'show'])->name('payslips.show');
        Route::get('/payslips/{payslip}/download', [EssPayslipController::class, 'download'])->name('payslips.download');
    });

    // Modul Struktur Organisasi & Penggajian (Super Admin & HR Admin)
    Route::middleware('role:super_admin|hr_admin')->group(function (): void {
        Route::resource('branches', BranchController::class);
        Route::resource('departments', DepartmentController::class);
        Route::resource('designations', DesignationController::class);
        Route::resource('employees', EmployeeController::class);
        Route::resource('shifts', ShiftController::class);
        Route::resource('salary-structures', SalaryStructureController::class)
            ->only(['index', 'edit', 'update'])
            ->parameters(['salary-structures' => 'employee']);
        Route::resource('payroll-batches', PayrollBatchController::class);
        Route::post('/payroll-batches/{payrollBatch}/approve', [PayrollBatchController::class, 'approve'])->name('payroll-batches.approve');
        Route::post('/payroll-batches/{payrollBatch}/pay', [PayrollBatchController::class, 'markAsPaid'])->name('payroll-batches.pay');
        Route::get('/payslips/{payslip}/download', [PayslipController::class, 'download'])->name('payslips.download');
        Route::get('/cash-advance-approvals', [CashAdvanceApprovalController::class, 'index'])->name('cash-advance-approvals.index');
        Route::post('/cash-advance-approvals/{cashAdvance}/approve', [CashAdvanceApprovalController::class, 'approve'])->name('cash-advance-approvals.approve');
        Route::post('/cash-advance-approvals/{cashAdvance}/reject', [CashAdvanceApprovalController::class, 'reject'])->name('cash-advance-approvals.reject');
        // Helper AJAX Cascading Dropdowns
        Route::get('/ajax/branches/{branch}/departments', [DepartmentController::class, 'byBranch'])->name('ajax.branches.departments');
        Route::get('/ajax/departments/{department}/designations', [DesignationController::class, 'byDepartment'])->name('ajax.departments.designations');
    });
    // Rute Khusus Super Admin
    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs');
    });

    // Monitoring Presensi, Persetujuan Cuti & Lembur (Manager, HR Admin, Super Admin)
    Route::middleware('role:super_admin|hr_admin|manager')->group(function (): void {
        Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
        Route::get('/leave-approvals', [LeaveApprovalController::class, 'index'])->name('leave-approvals.index');
        Route::get('/leave-approvals/{leaveRequest}', [LeaveApprovalController::class, 'show'])->name('leave-approvals.show');
        Route::post('/leave-approvals/{leaveRequest}/approve', [LeaveApprovalController::class, 'approve'])->name('leave-approvals.approve');
        Route::post('/leave-approvals/{leaveRequest}/reject', [LeaveApprovalController::class, 'reject'])->name('leave-approvals.reject');
        Route::get('/overtime-approvals', [OvertimeApprovalController::class, 'index'])->name('overtime-approvals.index');
        Route::post('/overtime-approvals/{overtime}/approve', [OvertimeApprovalController::class, 'approve'])->name('overtime-approvals.approve');
        Route::post('/overtime-approvals/{overtime}/reject', [OvertimeApprovalController::class, 'reject'])->name('overtime-approvals.reject');
    });
});
