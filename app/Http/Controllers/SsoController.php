<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SsoController extends Controller
{
    /**
     * URL SSO Server.
     */
    private string $ssoServerUrl = 'http://127.0.0.1:8001';

    /**
     * Callback SSO: terima token dari SSO Server, verifikasi, auto-login.
     */
    public function callback(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            Log::warning('SSO: Token tidak ditemukan di query string.');
            return redirect('/login')->with('error', 'Token SSO tidak ditemukan.');
        }

        try {
            // ============================================
            // 1. Verifikasi token ke SSO Server
            // ============================================
            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])
                ->get($this->ssoServerUrl . '/api/user');

            if (!$response->successful()) {
                Log::warning('SSO: Token tidak valid', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return redirect('/login')->with('error', 'Token SSO tidak valid atau sudah kadaluarsa.');
            }

            $data = $response->json();

            if (!isset($data['user'])) {
                Log::warning('SSO: Response tidak punya data user', $data);
                return redirect('/login')->with('error', 'Data user tidak lengkap dari SSO.');
            }

            $ssoUser = $data['user'];

            // ============================================
            // 2. Cari user di database SIKAP (fleksibel)
            // ============================================
            $user = $this->findLocalUser($ssoUser);

            if (!$user) {
                Log::warning('SSO: User tidak ditemukan di SIKAP', [
                    'email'    => $ssoUser['email']    ?? null,
                    'username' => $ssoUser['username'] ?? null,
                    'nip'      => $ssoUser['nip']      ?? null,
                ]);

                return redirect('/login')->with('error',
                    'Akun Anda belum terdaftar di SIKAP. Hubungi Administrator.');
            }

            // ============================================
            // 3. Cek status aktif user (kalau SIKAP punya kolom 'is_active')
            // ============================================
            if (Schema::hasColumn('users', 'is_active') && !$user->is_active) {
                Log::warning('SSO: User tidak aktif', ['user_id' => $user->id]);
                return redirect('/login')->with('error', 'Akun Anda tidak aktif. Hubungi Administrator.');
            }

            // ============================================
            // 4. Auto-login di SIKAP
            // ============================================
            Auth::login($user, true);
            $request->session()->regenerate();

            Log::info('SSO: User berhasil auto-login', [
                'user_id'  => $user->id,
                'username' => $user->username ?? null,
                'role'     => $user->role ?? null,
            ]);

            // ============================================
            // 5. Redirect ke dashboard sesuai role
            // ============================================
            return redirect($this->dashboardUrlFor($user));

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('SSO: Tidak bisa konek ke SSO Server', ['error' => $e->getMessage()]);
            return redirect('/login')->with('error', 'SSO Server tidak dapat dihubungi.');

        } catch (\Exception $e) {
            Log::error('SSO Callback Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect('/login')->with('error', 'Terjadi kesalahan saat verifikasi SSO.');
        }
    }

    /**
     * Cari user lokal di SIKAP berdasarkan identifier dari SSO.
     * Coba berurutan: email → username → nip.
     */
    private function findLocalUser(array $ssoUser): ?User
    {
        // Coba berdasarkan email
        if (!empty($ssoUser['email'])) {
            $user = User::where('email', $ssoUser['email'])->first();
            if ($user) return $user;
        }

        // Coba berdasarkan username
        if (!empty($ssoUser['username'])) {
            $user = User::where('username', $ssoUser['username'])->first();
            if ($user) return $user;
        }

        // Coba berdasarkan NIP (kalau SIKAP punya kolom nip)
        if (!empty($ssoUser['nip']) && Schema::hasColumn('users', 'nip')) {
            $user = User::where('nip', $ssoUser['nip'])->first();
            if ($user) return $user;
        }

        return null;
    }

    /**
     * Tentukan URL dashboard sesuai role user SIKAP.
     */
    private function dashboardUrlFor(User $user): string
    {
        // Ambil role user. Sesuaikan dengan struktur SIKAP Anda.
        // - Kalau kolom 'role' berupa string: $user->role
        // - Kalau pakai Spatie Permission: $user->getRoleNames()->first()
        $role = $user->role ?? null;

        return match ($role) {
            'admin'    => '/admin',
            'keuangan' => '/keuangan',
            'pimpinan' => '/pimpinan',
            'viewer'   => '/viewer',
            default    => '/viewer', // fallback paling aman
        };
    }
}
