<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function showForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Step 1: Kirim OTP ke email.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->where('active', true)->first();

        // Selalu balas sukses (mencegah email enumeration attack)
        if (!$user) {
            return response()->json([
                'ok' => true,
                'message' => 'Jika email terdaftar, kode OTP akan dikirim.',
            ]);
        }

        // Hapus OTP lama yang belum dipakai
        PasswordResetOtp::where('email', $user->email)->where('used', false)->delete();

        // Generate OTP 6 digit
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordResetOtp::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(15),
            'used' => false,
        ]);

        // Kirim email
        try {
            Mail::to($user->email)->send(new OtpMail($otp, $user->name, 15));
        } catch (\Throwable $e) {
            \Log::error('[OTP] Gagal kirim email: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'message' => 'Gagal mengirim email. Coba lagi nanti.',
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Kode OTP telah dikirim ke ' . $user->email,
        ]);
    }

    /**
     * Step 2: Verifikasi OTP & reset password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:12|confirmed',
        ]);

        $user = User::where('email', $request->email)->where('active', true)->first();
        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'Email tidak terdaftar.',
            ], 422);
        }

        // Cari OTP yang cocok
        $otpRecord = PasswordResetOtp::where('email', $user->email)
            ->where('otp', $request->otp)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'ok' => false,
                'message' => 'Kode OTP salah atau sudah kedaluwarsa.',
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Tandai OTP sudah dipakai
        $otpRecord->update(['used' => true]);

        return response()->json([
            'ok' => true,
            'message' => 'Password berhasil direset. Silakan login dengan password baru.',
        ]);
    }
}