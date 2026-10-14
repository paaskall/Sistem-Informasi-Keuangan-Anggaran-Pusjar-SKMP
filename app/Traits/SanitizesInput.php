<?php

namespace App\Traits;

trait SanitizesInput
{
    /**
     * Sanitasi input — hapus tag HTML berbahaya
     * Pakai di Form Request atau Controller
     */
    protected function sanitizeString(?string $value): ?string
    {
        if ($value === null) return null;
        
        // Hapus tag <script>, <iframe>, <object>, dll.
        $value = strip_tags($value, '<b><i><u><strong><em><br><p>');
        
        // Hapus karakter kontrol
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        return trim($value);
    }
}