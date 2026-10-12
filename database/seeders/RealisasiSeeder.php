<?php

namespace Database\Seeders;

use App\Models\KomponenBelanja;
use App\Models\Program;
use App\Models\Realisasi;
use App\Models\SubKegiatan;
use App\Models\Target;
use App\Models\User;
use Illuminate\Database\Seeder;

class RealisasiSeeder extends Seeder
{
    public function run(): void
    {
        $userKeuangan = User::where('username', 'keuangan')->first();

        // ============ REFERENSI ============
        $progFAC001 = Program::where('kode', '7916.FAC.001')->first();
        $progEBA994 = Program::where('kode', '7916.EBA.994')->first();

        $subFAC001_052 = SubKegiatan::where('program_id', $progFAC001->id)->where('kode', '052')->first();
        $subFAC001_053 = SubKegiatan::where('program_id', $progFAC001->id)->where('kode', '053')->first();
        $subFAC001_054 = SubKegiatan::where('program_id', $progFAC001->id)->where('kode', '054')->first();
        $subEBA994_001 = SubKegiatan::where('program_id', $progEBA994->id)->where('kode', '001')->first();

        $kompBahan = KomponenBelanja::where('kode', 'B.BAHAN')->first();
        $kompHonor = KomponenBelanja::where('kode', 'B.HONOR')->first();
        $kompPD = KomponenBelanja::where('kode', 'B.PD')->first();
        $kompOperasional = KomponenBelanja::where('kode', 'B.OPERASIONAL')->first();

        // ============ DATA ============
        $realisasis = [
            1 => [
                'tanggal' => '2026-01-15',
                'keterangan' => 'Realisasi awal Januari',
                'details' => [
                    ['prog' => $progFAC001, 'sub' => $subFAC001_052, 'komp' => $kompHonor, 'nominal' => 280_000_000],
                    ['prog' => $progFAC001, 'sub' => $subFAC001_052, 'komp' => $kompBahan, 'nominal' => 150_000_000],
                    ['prog' => $progFAC001, 'sub' => $subFAC001_052, 'komp' => $kompPD, 'nominal' => 200_000_000],
                    ['prog' => $progEBA994, 'sub' => $subEBA994_001, 'komp' => $kompOperasional, 'nominal' => 150_000_000],
                ],
            ],
            2 => [
                'tanggal' => '2026-02-20',
                'keterangan' => '',
                'details' => [
                    ['prog' => $progFAC001, 'sub' => $subFAC001_052, 'komp' => $kompHonor, 'nominal' => 250_000_000],
                    ['prog' => $progFAC001, 'sub' => $subFAC001_052, 'komp' => $kompBahan, 'nominal' => 130_000_000],
                    ['prog' => $progFAC001, 'sub' => $subFAC001_052, 'komp' => $kompPD, 'nominal' => 150_000_000],
                    ['prog' => $progEBA994, 'sub' => $subEBA994_001, 'komp' => $kompOperasional, 'nominal' => 150_000_000],
                ],
            ],
            3 => [
                'tanggal' => '2026-03-18',
                'keterangan' => '',
                'details' => [
                    ['prog' => $progFAC001, 'sub' => $subFAC001_053, 'komp' => $kompHonor, 'nominal' => 230_000_000],
                    ['prog' => $progFAC001, 'sub' => $subFAC001_053, 'komp' => $kompBahan, 'nominal' => 150_000_000],
                    ['prog' => $progFAC001, 'sub' => $subFAC001_053, 'komp' => $kompPD, 'nominal' => 160_000_000],
                    ['prog' => $progEBA994, 'sub' => $subEBA994_001, 'komp' => $kompOperasional, 'nominal' => 180_000_000],
                ],
            ],
            4 => [
                'tanggal' => '2026-04-22',
                'keterangan' => '',
                'details' => [
                    ['prog' => $progFAC001, 'sub' => $subFAC001_053, 'komp' => $kompHonor, 'nominal' => 300_000_000],
                    ['prog' => $progFAC001, 'sub' => $subFAC001_053, 'komp' => $kompBahan, 'nominal' => 150_000_000],
                    ['prog' => $progFAC001, 'sub' => $subFAC001_053, 'komp' => $kompPD, 'nominal' => 170_000_000],
                    ['prog' => $progEBA994, 'sub' => $subEBA994_001, 'komp' => $kompOperasional, 'nominal' => 200_000_000],
                ],
            ],
            5 => [
                'tanggal' => '2026-05-25',
                'keterangan' => '',
                'details' => [
                    ['prog' => $progFAC001, 'sub' => $subFAC001_054, 'komp' => $kompHonor, 'nominal' => 250_000_000],
                    ['prog' => $progFAC001, 'sub' => $subFAC001_054, 'komp' => $kompBahan, 'nominal' => 130_000_000],
                    ['prog' => $progFAC001, 'sub' => $subFAC001_054, 'komp' => $kompPD, 'nominal' => 200_000_000],
                    ['prog' => $progEBA994, 'sub' => $subEBA994_001, 'komp' => $kompOperasional, 'nominal' => 200_000_000],
                ],
            ],
        ];

        foreach ($realisasis as $bulan => $data) {
            $target = Target::where('tahun', 2026)->where('bulan', $bulan)->first();
            if (!$target) continue;

            $total = collect($data['details'])->sum('nominal');

            // Hapus realisasi lama untuk target ini (idempotent)
            Realisasi::where('target_id', $target->id)->forceDelete();

            $realisasi = Realisasi::create([
                'target_id' => $target->id,
                'tahun' => 2026,
                'bulan' => $bulan,
                'tanggal' => $data['tanggal'],
                'nominal' => $total,
                'keterangan' => $data['keterangan'],
                'user_id' => $userKeuangan?->id,
            ]);

            foreach ($data['details'] as $d) {
                $realisasi->details()->create([
                    'program_id' => $d['prog']->id,
                    'sub_kegiatan_id' => $d['sub']?->id,
                    'komponen_belanja_id' => $d['komp']->id,
                    'nominal' => $d['nominal'],
                ]);
            }
        }
    }
}