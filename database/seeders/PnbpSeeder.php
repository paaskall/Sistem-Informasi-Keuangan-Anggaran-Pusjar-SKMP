<?php

namespace Database\Seeders;

use App\Models\KategoriPnbp;
use App\Models\Pnbp;
use App\Models\PnbpDetail;
use Illuminate\Database\Seeder;

class PnbpSeeder extends Seeder
{
    public function run(): void
    {
        // Kategori PNBP
        $kategoris = [
            ['kode' => 'PNBP.DIKLAT', 'nama' => 'Pendapatan Diklat'],
            ['kode' => 'PNBP.SEWA', 'nama' => 'Sewa Sarana dan Prasarana'],
        ];

        foreach ($kategoris as $kat) {
            KategoriPnbp::updateOrCreate(['kode' => $kat['kode']], $kat);
        }

        $diklat = KategoriPnbp::where('kode', 'PNBP.DIKLAT')->first();
        $sewa = KategoriPnbp::where('kode', 'PNBP.SEWA')->first();

        // Sub-kategori Diklat — dengan NAMA yang jelas
        $subDiklat = [
            ['nama' => 'PKN II',              'target_bulan' => 1_600_000,   'peserta_target' => 70,   'peserta_real' => 70],
            ['nama' => 'PKA',                 'target_bulan' => 2_040_000,   'peserta_target' => 240,  'peserta_real' => 232],
            ['nama' => 'Latsar',              'target_bulan' => 12_424_000,  'peserta_target' => 2362, 'peserta_real' => 2362],
            ['nama' => 'Pelatihan AKPK',      'target_bulan' => 64_500,      'peserta_target' => 30,   'peserta_real' => 30],
            ['nama' => 'Uji Kompetensi',      'target_bulan' => 128_000,     'peserta_target' => 80,   'peserta_real' => 80],
        ];

        // PNBP per tahun
        foreach ([2024, 2025, 2026] as $tahun) {
            $pnbp = Pnbp::updateOrCreate(
                ['tahun' => $tahun],
                ['unit' => 'Bagian Keuangan']
            );

            // Hapus details lama (idempotent)
            $pnbp->details()->delete();

            for ($bulan = 1; $bulan <= 12; $bulan++) {
                // ===== PARENT: Diklat =====
                $parentDiklat = PnbpDetail::create([
                    'pnbp_id' => $pnbp->id,
                    'kategori_pnbp_id' => $diklat->id,
                    'bulan' => $bulan,
                    'target' => 0,
                    'realisasi' => 0,
                    'peserta_target' => 0,
                    'peserta_realisasi' => 0,
                    'keterangan' => null, // parent tidak punya keterangan
                ]);

                // ===== CHILDREN: Sub Diklat =====
                $totalTargetDiklat = 0;
                $totalRealDiklat = 0;
                $totalPesertaTarget = 0;
                $totalPesertaReal = 0;

                foreach ($subDiklat as $sub) {
                    // Variasi nominal 95%-105%
                    $variance = 0.95 + (mt_rand(0, 100) / 1000);
                    $target = $sub['target_bulan'] * $variance;
                    $real = $target * (0.95 + (mt_rand(0, 100) / 1000));

                    // Variasi peserta ±5%
                    $pTarget = $sub['peserta_target'];
                    $pReal = (int) round($sub['peserta_real'] * (0.95 + (mt_rand(0, 100) / 1000)));

                    PnbpDetail::create([
                        'pnbp_id' => $pnbp->id,
                        'kategori_pnbp_id' => $diklat->id,
                        'parent_id' => $parentDiklat->id,
                        'bulan' => $bulan,
                        'target' => round($target, 2),
                        'realisasi' => round($real, 2),
                        'peserta_target' => $pTarget,
                        'peserta_realisasi' => $pReal,
                        'keterangan' => $sub['nama'], // ⭐ INI YANG BIKIN NAMA MUNCUL
                    ]);

                    $totalTargetDiklat += $target;
                    $totalRealDiklat += $real;
                    $totalPesertaTarget += $pTarget;
                    $totalPesertaReal += $pReal;
                }

                // Update parent Diklat dengan total
                $parentDiklat->update([
                    'target' => round($totalTargetDiklat, 2),
                    'realisasi' => round($totalRealDiklat, 2),
                    'peserta_target' => $totalPesertaTarget,
                    'peserta_realisasi' => $totalPesertaReal,
                ]);

                // ===== PARENT: Sewa Sarana & Prasarana =====
                $targetSewa = 12_000_000 + mt_rand(-2_000_000, 3_000_000);
                $realSewa = $targetSewa * (0.9 + (mt_rand(0, 100) / 1000));

                PnbpDetail::create([
                    'pnbp_id' => $pnbp->id,
                    'kategori_pnbp_id' => $sewa->id,
                    'bulan' => $bulan,
                    'target' => $targetSewa,
                    'realisasi' => round($realSewa, 2),
                    'peserta_target' => 0,
                    'peserta_realisasi' => 0,
                    'keterangan' => null,
                ]);
            }
        }
    }
}
