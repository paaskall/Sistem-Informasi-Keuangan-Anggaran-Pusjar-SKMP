<?php

return [
    'sections' => [
        [
            'title' => 'Panduan Admin Keuangan',
            'icon' => 'shield-check',
            'items' => [
                [
                    'title' => 'Kelola User',
                    'content' => 'Buka menu Manajemen User → Kelola User. Anda bisa menambah, mengedit, menghapus, dan mengaktifkan user. Password baru minimal 12 karakter dengan huruf besar, huruf kecil, angka, dan simbol.',
                ],
                [
                    'title' => 'Master Data',
                    'content' => 'Menu Master Data berisi Program, Sub-Kegiatan, Komponen Belanja, Unit, Jenis Pengadaan, Tahapan, Status, dan Kategori Kendala. Semua data master dikelola di sini.',
                ],
                [
                    'title' => 'Audit Trail',
                    'content' => 'Semua aksi user dicatat otomatis. Buka menu Audit Trail untuk melihat riwayat login, CRUD, dan perubahan data.',
                ],
            ],
        ],
        [
            'title' => 'Panduan User Keuangan',
            'icon' => 'trending-up',
            'items' => [
                [
                    'title' => 'Input Target',
                    'content' => 'Buka menu Target → pilih tahun & bulan → input target per komponen belanja. Target harus diinput sebelum realisasi.',
                ],
                [
                    'title' => 'Input Realisasi',
                    'content' => 'Buka menu Realisasi → pilih target yang sudah dibuat → input realisasi per komponen. Realisasi akan otomatis dijumlahkan.',
                ],
                [
                    'title' => 'Kelola PNBP',
                    'content' => 'Menu PNBP digunakan untuk input target & realisasi Penerimaan Negara Bukan Pajak per kategori.',
                ],
                [
                    'title' => 'Pengadaan',
                    'content' => 'Input paket pengadaan dengan tahapan. Progress otomatis dihitung dari tahapan yang selesai.',
                ],
                [
                    'title' => 'Task Bulanan',
                    'content' => 'Menu Task Bulanan untuk reminder task bulanan. Bisa assign ke user, set deadline, dan ubah status.',
                ],
                [
                    'title' => 'Pengingat & AI',
                    'content' => 'Dashboard menampilkan widget pengingat untuk komponen yang belum 100%. Klik tombol rekomendasi AI untuk saran prioritas.',
                ],
                [
                    'title' => 'Laporkan Bug',
                    'content' => 'Buka menu Tentang Aplikasi → Laporkan Bug. Isi form dengan detail bug yang ditemukan. Tim developer akan menindaklanjuti.',
                ],
            ],
        ],
        [
            'title' => 'Panduan Pimpinan',
            'icon' => 'eye',
            'items' => [
                [
                    'title' => 'Dashboard Eksekutif',
                    'content' => 'Dashboard menampilkan ringkasan capaian, chart bulanan, progress pengadaan, dan analisa AI. Semua read-only.',
                ],
                [
                    'title' => 'Laporan',
                    'content' => 'Menu Laporan berisi target vs realisasi per bulan. Bisa export PDF atau CSV.',
                ],
                [
                    'title' => 'Audit Trail',
                    'content' => 'Lihat riwayat aktivitas user di menu Audit Trail (read-only).',
                ],
            ],
        ],
        [
            'title' => 'FAQ',
            'icon' => 'help-circle',
            'items' => [
                [
                    'title' => 'Lupa Password?',
                    'content' => 'Hubungi admin keuangan untuk reset password. User tidak bisa reset sendiri (keamanan).',
                ],
                [
                    'title' => 'Kenapa AI tidak muncul?',
                    'content' => 'AI Gemini butuh koneksi internet dan API key aktif. Hubungi admin kalau AI tidak muncul di widget pengingat.',
                ],
                [
                    'title' => 'Kenapa reminder kosong?',
                    'content' => 'Reminder muncul kalau ada komponen yang belum mencapai 100%. Kalau semua sudah 100%, reminder otomatis hidden.',
                ],
            ],
        ],
    ],
];