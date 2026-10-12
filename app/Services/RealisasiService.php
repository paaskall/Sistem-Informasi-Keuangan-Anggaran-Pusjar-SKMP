<?php

namespace App\Services;

use App\Models\Realisasi;
use App\Models\Target;
use Illuminate\Support\Facades\DB;
use App\Models\Kendala;

class RealisasiService
{
    /**
     * Ambil semua realisasi per tahun.
     */
    public function getRealisasisByYear(int $tahun): array
    {
        $realisasis = Realisasi::with([
            'target:id,tahun,bulan',
            'details.program:id,kode,nama',
            'details.subKegiatan:id,kode,nama',
            'details.komponenBelanja:id,kode,nama',
        ])
            ->where('tahun', $tahun)
            ->orderBy('bulan')
            ->get();

        return $realisasis->map(fn ($r) => $this->transformRealisasi($r))->toArray();
    }

    /**
     * Transform realisasi untuk frontend.
     */
    public function transformRealisasi(Realisasi $r): array
    {
        return [
            'id' => $r->id,
            'target_id' => $r->target_id,
            'tahun' => $r->tahun,
            'bulan' => $r->bulan,
            'bulan_nama' => $r->bulan_nama,
            'tanggal' => $r->tanggal?->format('Y-m-d'),
            'nominal' => (float) $r->nominal,
            'keterangan' => $r->keterangan,
            'user_id' => $r->user_id,
            'kendalas' => $r->kendalas->map(fn ($k) => [
                'id' => $k->id,
                'tanggal' => $k->tanggal?->format('Y-m-d'),
                'kategori_kendala_id' => $k->kategori_kendala_id,
                'kategori' => $k->kategoriKendala ? [
                    'id' => $k->kategoriKendala->id,
                    'nama' => $k->kategoriKendala->nama,
                ] : null,
                'komponen_belanja_id' => $k->komponen_belanja_id,
                'komponen_belanja' => $k->komponenBelanja ? [
                    'id' => $k->komponenBelanja->id,
                    'kode' => $k->komponenBelanja->kode,
                    'nama' => $k->komponenBelanja->nama,
                ] : null,
                'deskripsi' => $k->deskripsi,
                'dampak' => $k->dampak,
                'tindak_lanjut' => $k->tindak_lanjut,
                'status' => $k->status,
            ])->toArray(),
            'details' => $r->details->map(fn ($d) => [
                'id' => $d->id,
                'program_id' => $d->program_id,
                'program' => $d->program ? [
                    'id' => $d->program->id,
                    'kode' => $d->program->kode,
                    'nama' => $d->program->nama,
                ] : null,
                'sub_kegiatan_id' => $d->sub_kegiatan_id,
                'sub_kegiatan' => $d->subKegiatan ? [
                    'id' => $d->subKegiatan->id,
                    'kode' => $d->subKegiatan->kode,
                    'nama' => $d->subKegiatan->nama,
                ] : null,
                'komponen_belanja_id' => $d->komponen_belanja_id,
                'komponen_belanja' => $d->komponenBelanja ? [
                    'id' => $d->komponenBelanja->id,
                    'kode' => $d->komponenBelanja->kode,
                    'nama' => $d->komponenBelanja->nama,
                ] : null,
                'nominal' => (float) $d->nominal,
            ])->toArray(),
        ];
    }

    /**
     * Ambil target yang belum punya realisasi (untuk dropdown form create).
     */
    public function getAvailableTargets(int $tahun): array
    {
        $usedTargetIds = Realisasi::where('tahun', $tahun)->pluck('target_id')->toArray();

        return Target::where('tahun', $tahun)
            ->whereNotIn('id', $usedTargetIds)
            ->orderBy('bulan')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'tahun' => $t->tahun,
                'bulan' => $t->bulan,
                'bulan_nama' => $t->bulan_nama,
                'total' => $t->total_nominal,
                'details' => $t->details->map(fn ($d) => [
                    'program_id' => $d->program_id,
                    'program' => $d->program ? ['id' => $d->program->id, 'kode' => $d->program->kode, 'nama' => $d->program->nama] : null,
                    'sub_kegiatan_id' => $d->sub_kegiatan_id,
                    'sub_kegiatan' => $d->subKegiatan ? ['id' => $d->subKegiatan->id, 'kode' => $d->subKegiatan->kode, 'nama' => $d->subKegiatan->nama] : null,
                    'komponen_belanja_id' => $d->komponen_belanja_id,
                    'komponen_belanja' => $d->komponenBelanja ? ['id' => $d->komponenBelanja->id, 'kode' => $d->komponenBelanja->kode, 'nama' => $d->komponenBelanja->nama] : null,
                    'nominal' => (float) $d->nominal,
                ])->toArray(),
            ])
            ->toArray();
    }

    /**
     * Create realisasi + details.
     */
    public function createRealisasi(array $data, int $userId): Realisasi
    {
        return DB::transaction(function () use ($data, $userId) {
            $target = Target::findOrFail($data['target_id']);

            $realisasi = Realisasi::create([
                'target_id' => $target->id,
                'tahun' => $target->tahun,
                'bulan' => $target->bulan,
                'tanggal' => $data['tanggal'],
                'nominal' => collect($data['details'])->sum('nominal'),
                'keterangan' => $data['keterangan'] ?? null,
                'user_id' => $userId,
            ]);

            foreach ($data['details'] as $d) {
                $realisasi->details()->create([
                    'program_id' => $d['program_id'],
                    'sub_kegiatan_id' => $d['sub_kegiatan_id'] ?? null,
                    'komponen_belanja_id' => $d['komponen_belanja_id'],
                    'nominal' => $d['nominal'],
                ]);
            }

            // Handle kendala (opsional)
            if (!empty($data['kendalas'])) {
                $this->syncKendalas($realisasi, $target, $data['kendalas'], $userId);
            }

            return $realisasi->fresh(['details', 'kendalas']);
        });
    }

    /**
     * Update realisasi + replace details.
     */
    public function updateRealisasi(Realisasi $realisasi, array $data): Realisasi
    {
        return DB::transaction(function () use ($realisasi, $data) {
            $realisasi->update([
                'tanggal' => $data['tanggal'],
                'nominal' => collect($data['details'])->sum('nominal'),
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            $realisasi->details()->delete();

            foreach ($data['details'] as $d) {
                $realisasi->details()->create([
                    'program_id' => $d['program_id'],
                    'sub_kegiatan_id' => $d['sub_kegiatan_id'] ?? null,
                    'komponen_belanja_id' => $d['komponen_belanja_id'],
                    'nominal' => $d['nominal'],
                ]);
            }

            // Replace kendala (hapus semua, input ulang)
            $realisasi->kendalas()->delete();

            if (!empty($data['kendalas'])) {
                $target = Target::findOrFail($data['target_id']);
                $this->syncKendalas($realisasi, $target, $data['kendalas'], $realisasi->user_id ?? auth()->id());
            }

            return $realisasi->fresh(['details', 'kendalas']);
        });
    }

    /**
     * Hapus realisasi.
     */
    public function deleteRealisasi(Realisasi $realisasi): void
    {
        DB::transaction(function () use ($realisasi) {
            $realisasi->details()->delete();
            $realisasi->delete();
        });
    }

    /**
     * Hitung total realisasi tahunan.
     */
    public function getTotalTahunan(int $tahun): float
    {
        return (float) Realisasi::where('tahun', $tahun)->sum('nominal');
    }

    /**
     * Ambil target by ID dengan details (untuk form pre-fill).
     */
    public function getTargetWithDetails(int $targetId): ?array
    {
        $t = Target::with([
            'details.program:id,kode,nama',
            'details.subKegiatan:id,kode,nama',
            'details.komponenBelanja:id,kode,nama',
        ])->find($targetId);

        if (!$t) return null;

        return [
            'id' => $t->id,
            'tahun' => $t->tahun,
            'bulan' => $t->bulan,
            'bulan_nama' => $t->bulan_nama,
            'total' => $t->total_nominal,
            'details' => $t->details->map(fn ($d) => [
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
     * Sync kendala untuk realisasi.
     */
    protected function syncKendalas(Realisasi $realisasi, Target $target, array $kendalas, int $userId): void
    {
        foreach ($kendalas as $k) {
            if (empty($k['deskripsi'])) {
                continue; // skip yang kosong
            }

            Kendala::create([
                'tanggal' => $k['tanggal'] ?? $realisasi->tanggal,
                'periode' => $realisasi->bulan,
                'realisasi_id' => $realisasi->id,
                'target_id' => $target->id,
                'komponen_belanja_id' => $k['komponen_belanja_id'] ?? null,
                'kategori_kendala_id' => $k['kategori_kendala_id'],
                'deskripsi' => $k['deskripsi'],
                'dampak' => $k['dampak'] ?? null,
                'tindak_lanjut' => $k['tindak_lanjut'] ?? null,
                'status' => $k['status'] ?? 'Proses',
                'user_id' => $userId,
            ]);
        }
    }
}