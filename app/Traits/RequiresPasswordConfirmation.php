<?php

namespace App\Traits;

trait RequiresPasswordConfirmation
{
    /**
     * Cek apakah user baru konfirmasi password (dalam X menit terakhir).
     */
    protected function hasRecentPasswordConfirmation(int $timeoutMinutes = 5): bool
    {
        $confirmedAt = session('password_confirmed_at');

        if (!$confirmedAt) {
            return false;
        }

        return now()->diffInMinutes($confirmedAt) < $timeoutMinutes;
    }

    /**
     * Set konfirmasi password berhasil.
     */
    protected function setPasswordConfirmed(): void
    {
        session(['password_confirmed_at' => now()]);
    }

    /**
     * Clear konfirmasi password.
     */
    protected function clearPasswordConfirmation(): void
    {
        session()->forget('password_confirmed_at');
    }
}