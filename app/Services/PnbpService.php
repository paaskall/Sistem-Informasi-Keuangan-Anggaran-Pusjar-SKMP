<?php

namespace App\Services;

use App\Models\KategoriPnbp;
use App\Models\Pnbp;
use App\Models\PnbpDetail;
use Illuminate\Support\Facades\DB;

class PnbpService
{
    /**
     * Ambil data PNBP per tahun (struktur: bulan → kategori → sub).
     */
    public function getByYear(int $tahun): ?array
    {
        $pnbp = Pnbp::with([
            'details.kategori:id,kode,nama',
            'details.children.kategori:id,kode,nama',
        ])
            ->where('tahun', $tahun)
            ->first();

        if (!$pnbp) return null;

        return $this->transform($pnbp);
    }

    /**
     * Transform PNBP.
     */
    public function transform(Pnbp $p): array
    {
        // Group details per bulan → kategori
        $perBulan = [];

        // Ambil hanya parent (parent_id null)
        $parents = $p->details->whereNull('parent_id');

        foreach ($parents as $parent) {
            $bulan = $parent->bulan;
            $kid = $parent->kategori_pnbp_id;

            if (!isset($perBulan[$bulan])) {
                $perBulan[$bulan] = [
                    'bulan' => $bulan,
                    'bulan_nama' => $parent->bulan_nama,
                    'kategoris' => [],
                    'total_target' => 0,
                    'total_realisasi' => 0,
                ];
            }

            // Ambil children
            $children = $parent->children->map(function ($c) {
                return [
                    'id' => $c->id,
                    'nama' => $c->keterangan ?? '(Tanpa nama)',
                    'target' => (float) $c->target,
                    'realisasi' => (float) $c->realisasi,
                    'capaian' => $c->capaian,
                    'peserta_target' => $c->peserta_target,
                    'peserta_realisasi' => $c->peserta_realisasi,
                ];
            })->toArray();

            $perBulan[$bulan]['kategoris'][] = [
                'id' => $parent->id,
                'kategori_id' => $kid,
                'kode' => $parent->kategori?->kode ?? '-',
                'nama' => $parent->kategori?->nama ?? '-',
                'target' => (float) $parent->target,
                'realisasi' => (float) $parent->realisasi,
                'capaian' => $parent->capaian,
                'peserta_target' => (int) $parent->peserta_target,
                'peserta_realisasi' => (int) $parent->peserta_realisasi,
                'has_children' => count($children) > 0,
                'children' => $children,
            ];

            $perBulan[$bulan]['total_target'] += (float) $parent->target;
            $perBulan[$bulan]['total_realisasi'] += (float) $parent->realisasi;
        }

        // Sort per bulan
        ksort($perBulan);
        $perBulan = array_values($perBulan);

        // Hitung capaian per bulan
        foreach ($perBulan as &$b) {
            $b['capaian'] = $b['total_target'] > 0
                ? (int) round(($b['total_realisasi'] / $b['total_target']) * 100)
                : 0;
        }
        unset($b);

        return [
            'id' => $p->id,
            'tahun' => $p->tahun,
            'unit' => $p->unit,
            'total_target' => $p->total_target,
            'total_realisasi' => $p->total_realisasi,
            'capaian' => $p->capaian,
            'per_bulan' => $perBulan,
        ];
    }

    public function create(array $data): Pnbp
    {
        return DB::transaction(function () use ($data) {
            $pnbp = Pnbp::create([
                'tahun' => $data['tahun'],
                'unit' => $data['unit'] ?? 'Bagian Keuangan',
            ]);

            $kategoris = KategoriPnbp::all();
            foreach ($kategoris as $kat) {
                foreach (range(1, 12) as $bulan) {
                    $pnbp->details()->create([
                        'kategori_pnbp_id' => $kat->id,
                        'bulan' => $bulan,
                        'target' => 0,
                        'realisasi' => 0,
                        'peserta_target' => 0,
                        'peserta_realisasi' => 0,
                    ]);
                }
            }

            return $pnbp->fresh(['details.kategori']);
        });
    }

    public function updateDetail(int $detailId, array $data): PnbpDetail
    {
        $detail = PnbpDetail::findOrFail($detailId);

        $detail->update([
            'target' => $data['target'] ?? $detail->target,
            'realisasi' => $data['realisasi'] ?? $detail->realisasi,
            'peserta_target' => $data['peserta_target'] ?? $detail->peserta_target,
            'peserta_realisasi' => $data['peserta_realisasi'] ?? $detail->peserta_realisasi,
            'keterangan' => $data['keterangan'] ?? $detail->keterangan,
        ]);

        // Kalau ini child, update parent total
        if ($detail->parent_id) {
            $parent = PnbpDetail::find($detail->parent_id);
            if ($parent) {
                $totals = $parent->children()->selectRaw('SUM(target) as t, SUM(realisasi) as r')->first();
                $parent->update([
                    'target' => $totals->t ?? 0,
                    'realisasi' => $totals->r ?? 0,
                ]);
            }
        }

        return $detail->fresh(['kategori']);
    }

    public function deleteDetail(int $detailId): void
    {
        $detail = PnbpDetail::findOrFail($detailId);
        $parentId = $detail->parent_id;

        // Hapus children dulu kalau parent
        if (!$parentId) {
            $detail->children()->delete();
        }

        $detail->delete();

        // Update parent total kalau yang dihapus child
        if ($parentId) {
            $parent = PnbpDetail::find($parentId);
            if ($parent) {
                $totals = $parent->children()->selectRaw('SUM(target) as t, SUM(realisasi) as r')->first();
                $parent->update([
                    'target' => $totals->t ?? 0,
                    'realisasi' => $totals->r ?? 0,
                ]);
            }
        }
    }

    public function delete(Pnbp $pnbp): void
    {
        DB::transaction(function () use ($pnbp) {
            $pnbp->details()->delete();
            $pnbp->delete();
        });
    }

    public function getKategoris(): array
    {
        return KategoriPnbp::orderBy('kode')->get(['id', 'kode', 'nama'])->toArray();
    }

    public function getAvailableYears(): array
    {
        $years = Pnbp::orderByDesc('tahun')->pluck('tahun')->toArray();
        if (empty($years)) $years = [date('Y')];
        return $years;
    }
}
