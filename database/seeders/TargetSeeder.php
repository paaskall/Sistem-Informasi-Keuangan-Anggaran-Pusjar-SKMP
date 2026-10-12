<?php

namespace Database\Seeders;

use App\Models\KomponenBelanja;
use App\Models\Program;
use App\Models\SubKegiatan;
use App\Models\Target;
use Illuminate\Database\Seeder;

class TargetSeeder extends Seeder
{
    public function run(): void
    {
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

        // ============ TEMPLATE PER BULAN ============
        $targets = [
            1 => [
                ['prog' => $progFAC001, 'sub' => $subFAC001_052, 'komp' => $kompHonor, 'nominal' => 300_000_000],
                ['prog' => $progFAC001, 'sub' => $subFAC001_052, 'komp' => $kompBahan, 'nominal' => 150_000_000],
                ['prog' => $progFAC001, 'sub' => $subFAC001_052, 'komp' => $kompPD, 'nominal' => 200_000_000],
                ['prog' => $progEBA994, 'sub' => $subEBA994_001, 'komp' => $kompOperasional, 'nominal' => 200_000_000],
            ],
            2 => [
                ['prog' => $progFAC001, 'sub' => $subFAC001_052, 'komp' => $kompHonor, 'nominal' => 300_000_000],
                ['prog' => $progFAC001, 'sub' => $subFAC001_052, 'komp' => $kompBahan, 'nominal' => 150_000_000],
                ['prog' => $progFAC001, 'sub' => $subFAC001_052, 'komp' => $kompPD, 'nominal' => 150_000_000],
                ['prog' => $progEBA994, 'sub' => $subEBA994_001, 'komp' => $kompOperasional, 'nominal' => 200_000_000],
            ],
            3 => [
                ['prog' => $progFAC001, 'sub' => $subFAC001_053, 'komp' => $kompHonor, 'nominal' => 250_000_000],
                ['prog' => $progFAC001, 'sub' => $subFAC001_053, 'komp' => $kompBahan, 'nominal' => 150_000_000],
                ['prog' => $progFAC001, 'sub' => $subFAC001_053, 'komp' => $kompPD, 'nominal' => 180_000_000],
                ['prog' => $progEBA994, 'sub' => $subEBA994_001, 'komp' => $kompOperasional, 'nominal' => 250_000_000],
            ],
            4 => [
                ['prog' => $progFAC001, 'sub' => $subFAC001_053, 'komp' => $kompHonor, 'nominal' => 300_000_000],
                ['prog' => $progFAC001, 'sub' => $subFAC001_053, 'komp' => $kompBahan, 'nominal' => 150_000_000],
                ['prog' => $progFAC001, 'sub' => $subFAC001_053, 'komp' => $kompPD, 'nominal' => 180_000_000],
                ['prog' => $progEBA994, 'sub' => $subEBA994_001, 'komp' => $kompOperasional, 'nominal' => 220_000_000],
            ],
            5 => [
                ['prog' => $progFAC001, 'sub' => $subFAC001_054, 'komp' => $kompHonor, 'nominal' => 250_000_000],
                ['prog' => $progFAC001, 'sub' => $subFAC001_054, 'komp' => $kompBahan, 'nominal' => 130_000_000],
                ['prog' => $progFAC001, 'sub' => $subFAC001_054, 'komp' => $kompPD, 'nominal' => 200_000_000],
                ['prog' => $progEBA994, 'sub' => $subEBA994_001, 'komp' => $kompOperasional, 'nominal' => 200_000_000],
            ],
        ];

        // Bulan 6-12: flat sederhana
        foreach (range(6, 12) as $bulan) {
            $targets[$bulan] = [
                ['prog' => $progFAC001, 'sub' => $subFAC001_054, 'komp' => $kompHonor, 'nominal' => 250_000_000],
                ['prog' => $progEBA994, 'sub' => $subEBA994_001, 'komp' => $kompOperasional, 'nominal' => 200_000_000],
            ];
        }

        // ============ INSERT ============
        foreach ($targets as $bulan => $details) {
            $target = Target::updateOrCreate(
                ['tahun' => 2026, 'bulan' => $bulan],
                ['unit' => 'Bagian Keuangan']
            );

            // Hapus detail lama (idempotent)
            $target->details()->delete();

            foreach ($details as $d) {
                $target->details()->create([
                    'program_id' => $d['prog']->id,
                    'sub_kegiatan_id' => $d['sub']?->id,
                    'komponen_belanja_id' => $d['komp']->id,
                    'nominal' => $d['nominal'],
                ]);
            }
        }
    }
}