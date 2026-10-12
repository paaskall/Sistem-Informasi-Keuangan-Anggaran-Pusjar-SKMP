<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsConfirmed
{
    protected int $timeoutMinutes = 5;

    public function handle(Request $request, Closure $next): Response
    {
        $confirmedAt = session('password_confirmed_at');

        if ($confirmedAt && now()->diffInMinutes($confirmedAt) < $this->timeoutMinutes) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => false,
                'require_confirmation' => true,
                'message' => 'Aksi ini memerlukan konfirmasi password Anda.',
            ], 423);
        }

        return redirect()->back()->with('error', 'Aksi ini memerlukan konfirmasi password.');
    }
}
