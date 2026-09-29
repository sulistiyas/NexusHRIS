<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BranchApiController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Rute Publik Auth
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Rute Terproteksi Token (auth:sanctum)
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Master Cabang (Mobile Geofencing & Sync)
        Route::get('/branches', [BranchApiController::class, 'index']);
        Route::get('/branches/{branch}', [BranchApiController::class, 'show']);
        // Portal Profil Mandiri Karyawan (ESS) - Mobile API
        // Profil ESS Mobile (US-10)
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
        Route::get('/profile/emergency-contacts', [ProfileController::class, 'emergencyContacts']);
        Route::post('/profile/emergency-contacts', [ProfileController::class, 'storeEmergencyContact']);
        Route::delete('/profile/emergency-contacts/{id}', [ProfileController::class, 'destroyEmergencyContact']);
    });
});
