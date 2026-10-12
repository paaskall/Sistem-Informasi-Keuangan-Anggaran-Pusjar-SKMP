<?php

namespace App\Services;

use App\Models\KomponenBelanja;
use App\Models\Program;
use App\Models\SubKegiatan;
use App\Models\Target;
use App\Models\TargetDetail;
use Illuminate\Support\Facades\DB;
use App\Helpers\TahunHelper;

class TargetService
{

    /**
     * Cek apakah tahun masih bisa diedit.
     */
    public function isEditable(int $tahun): bool
    {
        return $tahun === (int) config('sikap.tahun_berjalan');
    }

    /**
     * Ambil semua target berdasarkan tahun, dengan details loaded.
     */
    public function getTargetsByYear(int $tahun): array
    {
        $targets = Target::with([
            'details.program:id,kode,nama',
            'details.subKegiatan:id,kode,nama',
            'details.komponenBelanja:id,kode,nama',
        ])
            ->where('tahun', $tahun)
            ->orderBy('bulan')
            ->get();

        return $targets->map(fn ($t) => $this->transformTarget($t))->toArray();
    }

    /**
     * Transform target untuk frontend.
     */
    public function transformTarget(Target $t): array
    {
        return [
            'id' => $t->id,
            'tahun' => $t->tahun,
            'editable' => TahunHelper::isEditable((int) $t->tahun),   
            'bulan' => $t->bulan,
            'bulan_nama' => $t->bulan_nama,
            'unit' => $t->unit,
            'total' => $t->total_nominal,
            'total_realisasi' => $t->total_realisasi,
            'capaian' => $t->capaian,
            'details' => $t->details->map(fn ($d) => [
                'id' => $d->id,
                'program_id' => $d->program_id,
                'program' => $d->program ? ['id' => $d->program->id, 'kode' => $d->program->kode, 'nama' => $d->program->nama] : null,
                'sub_kegiatan_id' => $d->sub_kegiatan_id,
                'sub_kegiatan' => $d->subKegiatan ? ['id' => $d->subKegiatan->id, 'kode' => $d->subKegiatan->kode, 'nama' => $d->subKegiatan->nama] : null,
                'komponen_belanja_id' => $d->komponen_belanja_id,
                'komponen_belanja' => $d->komponenBelanja ? ['id' => $d->komponenBelanja->id, 'kode' => $d->komponenBelanja->kode, 'nama' => $d->komponenBelanja->nama] : null,
                'nominal' => (float) $d->nominal,
            ])->toArray(),
        ];
    }

    /**
     * Simpan target baru + details.
     */
    public function createTarget(array $data): Target
    {
        return DB::transaction(function () use ($data) {
            $target = Target::create([
                'tahun' => $data['tahun'],
                'bulan' => $data['bulan'],
                'unit' => $data['unit'] ?? 'Bagian Keuangan',
            ]);

            foreach ($data['details'] as $detail) {
                $target->details()->create([
                    'program_id' => $detail['program_id'],
                    'sub_kegiatan_id' => $detail['sub_kegiatan_id'] ?? null,
                    'komponen_belanja_id' => $detail['komponen_belanja_id'],
                    'nominal' => $detail['nominal'],
                ]);
            }

            return $target->fresh(['details']);
        });
    }

    /**
     * Update target + replace details.
     */
    public function updateTarget(Target $target, array $data): Target
    {
        return DB::transaction(function () use ($target, $data) {
            $target->update([
                'tahun' => $data['tahun'],
                'bulan' => $data['bulan'],
                'unit' => $data['unit'] ?? $target->unit,
            ]);

            // Hapus semua detail lama, replace baru
            $target->details()->delete();

            foreach ($data['details'] as $detail) {
                $target->details()->create([
                    'program_id' => $detail['program_id'],
                    'sub_kegiatan_id' => $detail['sub_kegiatan_id'] ?? null,
                    'komponen_belanja_id' => $detail['komponen_belanja_id'],
                    'nominal' => $detail['nominal'],
                ]);
            }

            return $target->fresh(['details']);
        });
    }

    /**
     * Hapus target (soft delete).
     */
    public function deleteTarget(Target $target): void
    {
        DB::transaction(function () use ($target) {
            $target->details()->delete();
            $target->delete();
        });
    }

    /**
     * Reference data untuk dropdowns.
     */
    public function getReferenceData(): array
    {
        return [
            'programs' => Program::orderBy('kode')->get(['id', 'kode', 'nama'])->toArray(),
            'sub_kegiatans' => SubKegiatan::orderBy('kode')->get(['id', 'kode', 'nama', 'program_id'])->toArray(),
            'komponen_belanjas' => KomponenBelanja::orderBy('kode')->get(['id', 'kode', 'nama'])->toArray(),
        ];
    }

    /**
     * Hitung total target tahunan.
     */
    public function getTotalTahunan(int $tahun): float
    {
        return (float) TargetDetail::whereHas('target', fn ($q) => $q->where('tahun', $tahun))
            ->sum('nominal');
    }

    /**
     * Group details per program → sub-kegiatan (untuk accordion).
     */
    public function groupDetailsByProgram(array $details): array
    {
        $grouped = [];
        foreach ($details as $d) {
            $progId = $d['program_id'];
            $subId = $d['sub_kegiatan_id'] ?? 0;

            if (!isset($grouped[$progId])) {
                $grouped[$progId] = [
                    'program' => $d['program'],
                    'subs' => [],
                    'total' => 0,
                ];
            }

            if (!isset($grouped[$progId]['subs'][$subId])) {
                $grouped[$progId]['subs'][$subId] = [
                    'sub_kegiatan' => $d['sub_kegiatan'],
                    'komponens' => [],
                    'total' => 0,
                ];
            }

            $grouped[$progId]['subs'][$subId]['komponens'][] = [
                'komponen_belanja' => $d['komponen_belanja'],
                'nominal' => $d['nominal'],
            ];

            $grouped[$progId]['subs'][$subId]['total'] += $d['nominal'];
            $grouped[$progId]['total'] += $d['nominal'];
        }

        return $grouped;
    }
}