<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

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

    // Rute Khusus Super Admin
    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs');
    });
});
