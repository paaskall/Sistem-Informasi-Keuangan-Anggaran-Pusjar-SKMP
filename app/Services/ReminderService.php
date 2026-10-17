<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReminderService
{
    public function getReminders(?int $tahun = null, ?int $bulan = null): array
    {
        $tahun = $tahun ?? (int) date('Y');
        $bulan = $bulan ?? (int) date('n');

        $realisasi = $this->getRealisasiReminders($tahun, $bulan);
        $pnbp = $this->getPnbpReminders($tahun);

        return [
            'realisasi' => $realisasi,
            'pnbp' => $pnbp,
            'total' => $realisasi['count'] + $pnbp['count'],
            'is_late_month' => (int) date('j') >= 25,
            'periode' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'bulan_nama' => $this->bulanNama($bulan),
            ],
        ];
    }

    public function getRealisasiReminders(int $tahun, int $bulan): array
    {
        $targetId = DB::table('targets')
            ->where('tahun', $tahun)->where('bulan', $bulan)->whereNull('deleted_at')
            ->value('id');

        if (!$targetId) {
            return ['count' => 0, 'items' => [], 'message' => 'Belum ada target bulan ini'];
        }

        $details = DB::table('target_details as td')
            ->leftJoin('komponen_belanjas as kb', 'kb.id', '=', 'td.komponen_belanja_id')
            ->where('td.target_id', $targetId)
            ->select('td.komponen_belanja_id', 'td.nominal', 'kb.kode', 'kb.nama')
            ->get();

        if ($details->isEmpty()) {
            return ['count' => 0, 'items' => [], 'message' => 'Target tidak punya detail'];
        }

        $realisasiMap = DB::table('realisasi_details as rd')
            ->join('realisasis as r', 'r.id', '=', 'rd.realisasi_id')
            ->where('r.tahun', $tahun)->where('r.bulan', $bulan)->whereNull('r.deleted_at')
            ->select('rd.komponen_belanja_id', DB::raw('SUM(rd.nominal) as total'))
            ->groupBy('rd.komponen_belanja_id')
            ->pluck('total', 'komponen_belanja_id')
            ->toArray();

        $items = [];
        foreach ($details as $d) {
            $tgt = (float) $d->nominal;
            if ($tgt <= 0) continue;

            $real = (float) ($realisasiMap[$d->komponen_belanja_id] ?? 0);
            $pct = (int) round(($real / $tgt) * 100);

            if ($pct < 100) {
                $items[] = [
                    'komponen_id' => $d->komponen_belanja_id,
                    'kode' => $d->kode ?? '-',
                    'nama' => $d->nama ?? 'Tidak diketahui',
                    'target' => $tgt,
                    'realisasi' => $real,
                    'capaian' => $pct,
                ];
            }
        }

        usort($items, fn($a, $b) => $a['capaian'] <=> $b['capaian']);

        return [
            'count' => count($items),
            'items' => $items,
            'message' => count($items) . ' komponen belum 100%',
        ];
    }

    public function getPnbpReminders(int $tahun): array
    {
        $pnbpId = DB::table('pnbps')->where('tahun', $tahun)->whereNull('deleted_at')->value('id');

        if (!$pnbpId) {
            return ['count' => 0, 'exists' => false, 'items' => [], 'message' => 'Belum ada data PNBP'];
        }

        $details = DB::table('pnbp_details')
            ->where('pnbp_id', $pnbpId)->whereNull('parent_id')
            ->get();

        if ($details->isEmpty()) {
            return ['count' => 0, 'exists' => true, 'items' => [], 'message' => 'PNBP tidak punya detail'];
        }

        $kategoriIds = $details->pluck('kategori_pnbp_id')->unique()->filter()->toArray();
        $kategoriMap = [];
        $kodeMap = [];
        if (!empty($kategoriIds)) {
            $kategoriMap = DB::table('kategori_pnpbs')->whereIn('id', $kategoriIds)->pluck('nama', 'id')->toArray();
            $kodeMap = DB::table('kategori_pnpbs')->whereIn('id', $kategoriIds)->pluck('kode', 'id')->toArray();
        }

        $items = [];
        foreach ($details as $d) {
            $tgt = (float) $d->target;
            if ($tgt <= 0) continue;

            $real = (float) $d->realisasi;
            $pct = (int) round(($real / $tgt) * 100);

            if ($pct < 100) {
                $items[] = [
                    'kategori_id' => $d->kategori_pnbp_id,
                    'kode' => $kodeMap[$d->kategori_pnbp_id] ?? '-',
                    'nama' => $kategoriMap[$d->kategori_pnbp_id] ?? '-',
                    'target' => $tgt,
                    'realisasi' => $real,
                    'capaian' => $pct,
                ];
            }
        }

        usort($items, fn($a, $b) => $a['capaian'] <=> $b['capaian']);

        return [
            'count' => count($items),
            'exists' => true,
            'items' => $items,
            'message' => count($items) . ' kategori belum 100%',
        ];
    }

    public function getReminderCount(): int
    {
        return $this->getReminders()['total'];
    }

    protected function bulanNama(int $bulan): string
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ][$bulan] ?? '-';
    }
}