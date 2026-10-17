<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    protected int $maxAttempts = 5;
    protected int $decayMinutes = 15;

    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect($this->redirectTo());
        }
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            $this->logSecurityEvent('LOGIN_BLOCKED', $request->username, "Block {$minutes} menit.");

            return back()
                ->withInput($request->only('username'))
                ->with('error', "Terlalu banyak percobaan. Coba lagi dalam {$minutes} menit.");
        }

        $user = User::where('username', $credentials['username'])->where('active', true)->first();

        if (!$user || !Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, $this->decayMinutes * 60);
            $remaining = $this->maxAttempts - RateLimiter::attempts($throttleKey);
            $this->logSecurityEvent('LOGIN_FAILED', $request->username, "Sisa: {$remaining}");

            $message = 'Username atau password salah.';
            if ($remaining > 0 && $remaining <= 2) {
                $message .= " Sisa kesempatan: {$remaining}";
            }
            return back()->withInput($request->only('username'))->with('error', $message);
        }

        // Login sukses
        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        // ⭐ Set flag SweetAlert pengingat (khusus keuangan)
        if ($user->role === 'keuangan') {
            $request->session()->put('show_login_reminder', true);
        }

        $this->logSecurityEvent('LOGIN_SUCCESS', $user->username, "Login dari IP " . $request->ip());

        return redirect()
            ->intended($this->redirectTo())
            ->with('success', 'Login berhasil. Selamat datang, ' . Auth::user()->name);
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            $this->logSecurityEvent('LOGOUT', $user->username, "Logout dari IP " . $request->ip());
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar dari sistem.');
    }

    protected function redirectTo(): string
    {
        $user = Auth::user();
        if (!$user) return route('login');

        return match ($user->role) {
            'admin_keuangan' => route('admin.dashboard'),
            'keuangan'       => route('keuangan.dashboard'),
            'pimpinan'       => route('pimpinan.dashboard'),
            'user'           => route('user.dashboard'),
            default          => route('login'),
        };
    }

    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(
            Str::lower($request->input('username')) . '|' . $request->ip()
        );
    }

    protected function logSecurityEvent(string $action, string $username, string $description): void
    {
        try {
            AuditTrail::create([
                'user_id' => Auth::id(),
                'user_name' => $username,
                'action' => 'UPDATE',
                'module' => 'Auth',
                'record_id' => strtoupper($action),
                'before' => null,
                'after' => $description,
                'ip' => request()->ip() ?? '127.0.0.1',
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to log security event: ' . $e->getMessage());
        }
    }
}