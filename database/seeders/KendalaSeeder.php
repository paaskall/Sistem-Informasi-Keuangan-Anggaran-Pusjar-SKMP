<?php

namespace Database\Seeders;

use App\Models\KategoriKendala;
use App\Models\Kendala;
use App\Models\User;
use Illuminate\Database\Seeder;

class KendalaSeeder extends Seeder
{
    public function run(): void
    {
        $userKeuangan = User::where('username', 'keuangan')->first();

        $admin = KategoriKendala::where('nama', 'Administrasi')->first();
        $teknis = KategoriKendala::where('nama', 'Teknis')->first();

        $kendalas = [
            [
                'tanggal' => '2026-09-15',
                'periode' => 9,
                'kategori_kendala_id' => $admin->id,
                'deskripsi' => 'Dokumen belum lengkap',
                'dampak' => 'Menghambat pencairan',
                'tindak_lanjut' => 'Melengkapi dokumen',
                'status' => 'Proses',
                'user_id' => $userKeuangan?->id,
            ],
            [
                'tanggal' => '2026-09-20',
                'periode' => 9,
                'kategori_kendala_id' => $teknis->id,
                'deskripsi' => 'Spesifikasi belum final',
                'dampak' => 'Keterlambatan pengadaan',
                'tindak_lanjut' => 'Rapat koordinasi',
                'status' => 'Selesai',
                'user_id' => $userKeuangan?->id,
            ],
        ];

        foreach ($kendalas as $data) {
            Kendala::updateOrCreate(
                ['deskripsi' => $data['deskripsi'], 'tanggal' => $data['tanggal']],
                $data
            );
        }
    }
}