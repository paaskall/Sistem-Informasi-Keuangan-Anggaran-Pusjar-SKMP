<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Tab Master Data
    |--------------------------------------------------------------------------
    |
    | Setiap tab mendefinisikan:
    | - key: nama properti di DB (plural)
    | - label: judul tab
    | - icon: nama icon Lucide
    | - model: FQCN model Eloquent
    | - fields: definisi kolom untuk form & validasi
    |
    */

    'tabs' => [
        'program' => [
            'label' => 'Program',
            'icon' => 'folder-kanban',
            'model' => \App\Models\Program::class,
            'fields' => [
                ['key' => 'kode', 'label' => 'Kode', 'type' => 'text', 'required' => true, 'placeholder' => '7916.FAC.001', 'mono' => true],
                ['key' => 'nama', 'label' => 'Nama Program', 'type' => 'text', 'required' => true, 'placeholder' => 'Pelatihan Struktural Kepemimpinan'],
            ],
        ],

        'subKegiatan' => [
            'label' => 'Sub-Kegiatan',
            'icon' => 'git-branch',
            'model' => \App\Models\SubKegiatan::class,
            'fields' => [
                ['key' => 'program_id', 'label' => 'Program', 'type' => 'select', 'required' => true,
                 'source' => 'program', 'source_label' => 'nama', 'source_value' => 'id', 'source_prefix' => 'kode'],
                ['key' => 'kode', 'label' => 'Kode', 'type' => 'text', 'required' => true, 'placeholder' => '052', 'mono' => true],
                ['key' => 'nama', 'label' => 'Nama Sub-Kegiatan', 'type' => 'text', 'required' => true, 'placeholder' => 'Pelaksanaan Pelatihan Kepemimpinan Nasional Tk. II'],
            ],
        ],

        'komponenBelanja' => [
            'label' => 'Komponen Belanja',
            'icon' => 'receipt',
            'model' => \App\Models\KomponenBelanja::class,
            'fields' => [
                ['key' => 'kode', 'label' => 'Kode', 'type' => 'text', 'required' => true, 'placeholder' => 'B.HONOR', 'mono' => true],
                ['key' => 'nama', 'label' => 'Nama Komponen', 'type' => 'text', 'required' => true, 'placeholder' => 'Belanja Honor Output Kegiatan'],
            ],
        ],

        'unit' => [
            'label' => 'Unit',
            'icon' => 'building-2',
            'model' => \App\Models\Unit::class,
            'fields' => [
                ['key' => 'nama', 'label' => 'Nama Unit', 'type' => 'text', 'required' => true, 'placeholder' => 'Bagian Keuangan'],
            ],
        ],

        'jenisPengadaan' => [
            'label' => 'Jenis Pengadaan',
            'icon' => 'tags',
            'model' => \App\Models\JenisPengadaan::class,
            'fields' => [
                ['key' => 'nama', 'label' => 'Nama Jenis', 'type' => 'text', 'required' => true, 'placeholder' => 'Kontrak'],
            ],
        ],

        'tahapan' => [
            'label' => 'Tahapan',
            'icon' => 'list-ordered',
            'model' => \App\Models\Tahapan::class,
            'fields' => [
                ['key' => 'nama', 'label' => 'Nama Tahapan', 'type' => 'text', 'required' => true, 'placeholder' => 'Tender'],
            ],
        ],

        'status' => [
            'label' => 'Status',
            'icon' => 'badge-check',
            'model' => \App\Models\Status::class,
            'fields' => [
                ['key' => 'nama', 'label' => 'Nama Status', 'type' => 'text', 'required' => true, 'placeholder' => 'Proses'],
            ],
        ],

        'kategoriKendala' => [
            'label' => 'Kategori Kendala',
            'icon' => 'alert-octagon',
            'model' => \App\Models\KategoriKendala::class,
            'fields' => [
                ['key' => 'nama', 'label' => 'Nama Kategori', 'type' => 'text', 'required' => true, 'placeholder' => 'Administrasi'],
            ],
        ],
    ],
];