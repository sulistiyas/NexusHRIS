<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Tampilkan formulir login.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Proses upaya login pengguna web.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        // 1. Periksa apakah akun dinonaktifkan
        if ($user && ! $user->is_active) {
            return back()->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
            ])->onlyInput('email');
        }

        // 2. Coba autentikasi
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'Email atau kata sandi yang Anda masukkan salah.',
            ])->onlyInput('email');
        }

        // 3. Amankan sesi
        $request->session()->regenerate();

        // 4. Catat aktivitas login web ke activity_logs
        ActivityLog::create([
            'user_id' => Auth::id(),
            'event' => 'web_login',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->intended(route('dashboard'));
    }
}
