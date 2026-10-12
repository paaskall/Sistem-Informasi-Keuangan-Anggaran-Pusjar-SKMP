<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class ConfirmPasswordController extends Controller
{
    /**
     * Max attempt konfirmasi password.
     */
    protected int $maxAttempts = 3;

    /**
     * Durasi block (menit) kalau gagal.
     */
    protected int $decayMinutes = 5;

    /**
     * Konfirmasi password admin untuk aksi sensitif.
     */
    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if (!$user->isAdmin()) {
            return response()->json([
                'ok' => false,
                'message' => 'Hanya admin yang bisa melakukan aksi ini.',
            ], 403);
        }

        // Rate limit
        $throttleKey = 'confirm-password:' . $user->id;

        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxAttempts)) {
            $minutes = ceil(RateLimiter::availableIn($throttleKey) / 60);

            $this->logSecurityEvent($user, 'PASSWORD_CONFIRM_BLOCKED', "Block {$minutes} menit");

            return response()->json([
                'ok' => false,
                'message' => "Terlalu banyak percobaan. Coba lagi dalam {$minutes} menit.",
            ], 429);
        }

        // Cek password
        if (!Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, $this->decayMinutes * 60);
            $remaining = $this->maxAttempts - RateLimiter::attempts($throttleKey);

            $this->logSecurityEvent(
                $user,
                'PASSWORD_CONFIRM_FAILED',
                "Password salah. Sisa: {$remaining}"
            );

            return response()->json([
                'ok' => false,
                'message' => "Password salah. Sisa kesempatan: {$remaining}",
            ], 422);
        }

        // Sukses
        RateLimiter::clear($throttleKey);
        session(['password_confirmed_at' => now()]);

        $this->logSecurityEvent($user, 'PASSWORD_CONFIRM_SUCCESS', 'Konfirmasi berhasil');

        return response()->json([
            'ok' => true,
            'message' => 'Konfirmasi berhasil.',
            'expires_at' => now()->addMinutes(5)->timestamp,
        ]);
    }

    /**
     * Cek status konfirmasi.
     */
    public function status(): JsonResponse
    {
        $confirmedAt = session('password_confirmed_at');
        $isConfirmed = $confirmedAt && now()->diffInMinutes($confirmedAt) < 5;

        return response()->json([
            'ok' => true,
            'is_confirmed' => $isConfirmed,
            'expires_at' => $isConfirmed ? now()->parse($confirmedAt)->addMinutes(5)->timestamp : null,
        ]);
    }

    /**
     * Log security event.
     */
    protected function logSecurityEvent($user, string $action, string $description): void
    {
        try {
            AuditTrail::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'UPDATE',
                'module' => 'Security',
                'record_id' => strtoupper($action),
                'before' => null,
                'after' => $description,
                'ip' => request()->ip() ?? '127.0.0.1',
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed log security event: ' . $e->getMessage());
        }
    }
}