<?php

namespace App\Helpers;

class TahunHelper
{
    public static function tahunBerjalan(): int
    {
        return (int) date('Y');
    }

    public static function isEditable(int $tahun): bool
    {
        return $tahun === self::tahunBerjalan();
    }

    public static function isArsip(int $tahun): bool
    {
        return !self::isEditable($tahun);
    }
}