<?php

namespace Database\Seeders;

use App\Models\JenisPengadaan;
use App\Models\Pengadaan;
use App\Models\Status;
use Illuminate\Database\Seeder;

class PengadaanSeeder extends Seeder
{
    public function run(): void
    {
        $kontrak = JenisPengadaan::where('nama', 'Kontrak')->first();
        $barang = JenisPengadaan::where('nama', 'Barang')->first();
        $jasa = JenisPengadaan::where('nama', 'Jasa')->first();

        $proses = Status::where('nama', 'Proses')->first();
        $selesai = Status::where('nama', 'Selesai')->first();

        $pengadaans = [
            ['kode' => 'PGD-00045', 'nama' => 'Kontrak A', 'jenis_pengadaan_id' => $kontrak->id, 'status_id' => $proses->id, 'progress' => 80],
            ['kode' => 'PGD-00046', 'nama' => 'Tender B', 'jenis_pengadaan_id' => $kontrak->id, 'status_id' => $proses->id, 'progress' => 40],
            ['kode' => 'PGD-00047', 'nama' => 'Pengadaan Barang C', 'jenis_pengadaan_id' => $barang->id, 'status_id' => $selesai->id, 'progress' => 100],
            ['kode' => 'PGD-00048', 'nama' => 'Jasa Konsultan D', 'jenis_pengadaan_id' => $jasa->id, 'status_id' => $proses->id, 'progress' => 25],
        ];

        foreach ($pengadaans as $data) {
            Pengadaan::updateOrCreate(['kode' => $data['kode']], $data);
        }
    }
}