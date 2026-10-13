<?php

namespace Database\Seeders;

use App\Models\AkselerasiPenyerapan;
use Illuminate\Database\Seeder;

class AkselerasiPenyerapanSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'kegiatan' => 'Percepatan penyelesaian dokumen pencairan anggaran',
                'jangka_waktu' => 'Oktober 2026',
                'progress' => 75,
                'keterangan' => 'Koordinasi kelengkapan dokumen dengan unit terkait',
                'urutan' => 1,
            ],
            [
                'kegiatan' => 'Percepatan proses pengadaan barang/jasa',
                'jangka_waktu' => 'Oktober–November 2026',
                'progress' => 60,
                'keterangan' => 'Pemantauan jadwal dan tindak lanjut dengan penyedia',
                'urutan' => 2,
            ],
            [
                'kegiatan' => 'Monitoring realisasi anggaran setiap minggu',
                'jangka_waktu' => 'Minggu s.d Desember 2026',
                'progress' => 50,
                'keterangan' => 'Evaluasi deviasi target dan tindak lanjut kendala',
                'urutan' => 3,
            ],
        ];

        foreach ($items as $item) {
            AkselerasiPenyerapan::create($item);
        }
    }
}