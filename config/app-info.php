<?php

return [
    'name' => 'SIKAP',
    'full_name' => 'Sistem Informasi Keuangan & Anggaran Pusjar SKMP',
    'description' => 'Aplikasi terintegrasi untuk pengelolaan target, realisasi anggaran, PNBP, pengadaan, dan analisa keuangan menggunakan kecerdasan buatan (AI) Gemini. Dirancang khusus untuk mendukung efisiensi kerja di lingkungan Pusjar SKMP LAN RI.',
    'version' => '1.0.0',
    'release_date' => '2026-01-15',
    'institution' => 'Lembaga Administrasi Negara RI',
    'unit' => 'Pusat Jaringan dan Kerja Sama Manajemen Pemerintahan (Pusjar SKMP)',
    'logo' => 'assets/logo-lanri.png',

    'features' => [
        ['icon' => 'target', 'title' => 'Kelola Target & Realisasi', 'desc' => 'Input dan pantau target anggaran bulanan dengan mudah'],
        ['icon' => 'receipt', 'title' => 'PNBP & Pengadaan', 'desc' => 'Kelola Penerimaan Negara Bukan Pajak dan paket pengadaan'],
        ['icon' => 'bell-ring', 'title' => 'Pengingat Otomatis', 'desc' => 'Notifikasi otomatis untuk komponen yang belum 100%'],
        ['icon' => 'sparkles', 'title' => 'Analisa AI Gemini', 'desc' => 'Rekomendasi cerdas prioritas realisasi dengan AI'],
        ['icon' => 'users', 'title' => 'Multi-Role', 'desc' => 'Akses berbeda untuk Admin, Keuangan, Pimpinan, User'],
        ['icon' => 'shield-check', 'title' => 'Keamanan Berlapis', 'desc' => 'Audit trail, CSRF protection, dan password policy ketat'],
    ],

    'team' => [
        ['name' => 'Paskal Arvito Weni', 'role' => 'Full-Stack Developer', 'initial' => 'P'],
    ],

    'links' => [
        ['label' => 'Dokumentasi', 'url' => '#', 'icon' => 'book-open'],
        ['label' => 'Changelog', 'url' => '#', 'icon' => 'list'],
        ['label' => 'Laporkan Bug', 'url' => 'mailto:sikap@lan.go.id', 'icon' => 'bug'],
    ],

    'copyright' => '© 2026 LAN RI. All rights reserved.',
    'tagline' => 'Dibangun untuk efisiensi kerja pengelolaan keuangan',
];