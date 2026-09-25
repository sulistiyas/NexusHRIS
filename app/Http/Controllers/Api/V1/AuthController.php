<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends BaseApiController
{
    /**
     * Login via REST API Mobile & terbitkan token Sanctum.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        // 1. Cek kredensial email & password
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return $this->sendError(
                'Kredensial yang diberikan salah.',
                ['email' => ['Email atau kata sandi tidak cocok.']],
                Response::HTTP_UNAUTHORIZED
            );
        }

        // 2. Pemeriksaan akun aktif (is_active)
        if (! $user->is_active) {
            return $this->sendError(
                'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
                null,
                Response::HTTP_FORBIDDEN
            );
        }

        // 3. Catat log login ke tabel activity_logs
        ActivityLog::create([
            'user_id' => $user->id,
            'event' => 'api_login',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // 4. Buat Personal Access Token Sanctum
        $deviceName = $credentials['device_name'] ?? 'mobile_device';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->sendSuccess([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'Login berhasil.');
    }

    /**
     * Ambil profil pengguna saat ini.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->sendSuccess(
            new UserResource($request->user()),
            'Data pengguna berhasil diambil.'
        );
    }

    /**
     * Logout & revoke token aktif saat ini.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        // Catat log logout
        ActivityLog::create([
            'user_id' => $user->id,
            'event' => 'api_logout',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Hapus token yang sedang digunakan untuk request ini
        $user->currentAccessToken()->delete();

        return $this->sendSuccess(null, 'Logout berhasil, token telah dicabut.');
    }
}
