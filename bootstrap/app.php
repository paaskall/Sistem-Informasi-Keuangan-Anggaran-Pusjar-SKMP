<?php

use App\Http\Middleware\EnsurePasswordIsConfirmed;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ============================================================
        // MIDDLEWARE ALIAS
        // ============================================================
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'password.confirm' => EnsurePasswordIsConfirmed::class,
        ]);

        // ============================================================
        // SECURITY HEADERS (global, semua request)
        // ============================================================
        $middleware->prepend(SecurityHeadersMiddleware::class);

        // ============================================================
        // REDIRECT GUESTS & USERS
        // ============================================================
        $middleware->redirectGuestsTo('/login');

        // ============================================================
        // TRUST PROXY (Cloudflare Tunnel)
        // Tanpa ini Laravel mengira request = http:// sehingga route()
        // menghasilkan URL http:// dan cookie sesi tidak ikut terkirim.
        // Untuk production sungguhan, ganti '*' dengan IP proxy Anda.
        // ============================================================
        $middleware->trustProxies(at: '*');

        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            return match ($user?->role) {
                'admin_keuangan' => '/admin',
                'keuangan'       => '/keuangan',
                'pimpinan'       => '/pimpinan',
                'user'           => '/user',
                default          => '/',
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ============================================================
        // CUSTOM RENDER UNTUK AJAX / JSON REQUEST
        // ============================================================
        $exceptions->render(function (Throwable $e, Request $request) {
            // Kalau AJAX / API request → return JSON
            if ($request->expectsJson() || $request->ajax()) {
                // Validation Exception — 422
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Validasi gagal.',
                        'errors' => $e->errors(),
                    ], 422);
                }

                // Belum login / sesi berakhir — 401 (bukan 500)
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Sesi Anda berakhir. Silakan login kembali.',
                        'status' => 401,
                    ], 401);
                }

                $status = 500;
                $message = 'Terjadi kesalahan server.';

                if ($e instanceof HttpExceptionInterface) {
                    $status = $e->getStatusCode();
                    $message = $e->getMessage() ?: match ($status) {
                        401 => 'Anda belum login.',
                        403 => 'Anda tidak memiliki akses.',
                        404 => 'Data tidak ditemukan.',
                        405 => 'Metode HTTP tidak didukung.',
                        419 => 'Sesi Anda telah berakhir. Silakan refresh halaman.',
                        422 => 'Data yang dikirim tidak valid.',
                        423 => 'Aksi ini memerlukan konfirmasi password.',
                        429 => 'Terlalu banyak permintaan. Coba lagi nanti.',
                        default => 'Terjadi kesalahan.',
                    };
                }

                return response()->json([
                    'ok' => false,
                    'message' => $message,
                    'status' => $status,
                ], $status);
            }

            return null; // lanjut ke default handler (untuk web request → tampil error page)
        });
    })->create();