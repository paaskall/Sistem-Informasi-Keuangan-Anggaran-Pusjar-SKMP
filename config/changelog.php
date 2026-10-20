<?php

return [
    'releases' => [
        [
            'version' => '1.2.0',
            'date' => '2026-10-19',
            'type' => 'minor',
            'title' => 'AI Gemini & Pengingat Otomatis',
            'changes' => [
                'new' => [
                    'Analisa prioritas realisasi dengan AI Gemini',
                    'Widget pengingat otomatis di dashboard',
                    'Notifikasi SweetAlert saat login',
                    'Halaman Tentang Aplikasi',
                    'Halaman Dokumentasi (admin)',
                    'Halaman Changelog (admin)',
                    'Fitur Laporkan Bug untuk user keuangan',
                ],
                'fixed' => [
                    'Bug render widget pengingat',
                    'Cache config Gemini tidak update',
                    'Model Gemini deprecated (1.5 → 3.8-flash)',
                ],
                'breaking' => [],
            ],
        ],
        [
            'version' => '1.1.0',
            'date' => '2026-09-15',
            'type' => 'minor',
            'title' => 'Task Bulanan & Multi-Role',
            'changes' => [
                'new' => [
                    'Fitur Task Bulanan (kalender + CRUD)',
                    'Rename role: viewer → user, admin → admin_keuangan',
                    'Widget Task Mendatang di dashboard',
                    'Halaman profile dengan avatar upload',
                ],
                'fixed' => [
                    'Perbaikan validasi password',
                    'Perbaikan redirect login per role',
                ],
                'breaking' => [
                    'Role viewer dan admin harus di-migrate ke user dan admin_keuangan',
                ],
            ],
        ],
        [
            'version' => '1.0.0',
            'date' => '2026-08-01',
            'type' => 'major',
            'title' => 'Rilis Pertama SIKAP',
            'changes' => [
                'new' => [
                    'Multi-role: Admin, Keuangan, Pimpinan, User',
                    'Modul Target & Realisasi',
                    'Modul PNBP',
                    'Modul Pengadaan',
                    'Modul Kendala',
                    'Modul Laporan (PDF & CSV)',
                    'Audit Trail',
                    'Manajemen User',
                ],
                'fixed' => [],
                'breaking' => [],
            ],
        ],
    ],
];