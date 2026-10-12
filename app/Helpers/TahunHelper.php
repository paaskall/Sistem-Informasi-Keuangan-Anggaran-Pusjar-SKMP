<?php

namespace App\Helpers;

class TahunHelper
{
    /**
     * Cek apakah tahun masih bisa diedit.
     */
    public static function isEditable(int $tahun): bool
    {
        return $tahun === self::tahunBerjalan();
    }

    /**
     * Tahun anggaran berjalan.
     */
    public static function tahunBerjalan(): int
    {
        return (int) config('sikap.tahun_berjalan', date('Y'));
    }

    /**
     * Cek apakah tahun = arsip (read-only).
     */
    public static function isArsip(int $tahun): bool
    {
        return !self::isEditable($tahun);
    }
}