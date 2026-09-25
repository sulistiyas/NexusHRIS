<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * Proses logout pengguna web.
     */
    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        // 1. Catat log logout jika user teridentifikasi
        if ($userId) {
            ActivityLog::create([
                'user_id' => $userId,
                'event' => 'web_logout',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // 2. Bersihkan sesi dan token CSRF
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar.');
    }
}
