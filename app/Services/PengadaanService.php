<?php

namespace App\Services;

use App\Models\Pengadaan;
use App\Models\PengadaanTahapan;
use App\Models\Status;
use Illuminate\Support\Facades\DB;

class PengadaanService
{
    /**
     * Generate kode pengadaan berikutnya.
     */
    public function generateKode(): string
    {
        $last = Pengadaan::withTrashed()
            ->where('kode', 'like', 'PGD-%')
            ->orderByDesc('id')
            ->first();

        $nextNum = 1;
        if ($last) {
            $num = (int) substr($last->kode, 4);
            $nextNum = $num + 1;
        }

        return 'PGD-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Template tahapan standar.
     */
    public function getTemplateTahapan(): array
    {
        return [
            'Perencanaan',
            'Pemilihan Penyedia',
            'Penetapan',
            'Kontrak',
            'Serah Terima',
        ];
    }

    /**
     * Ambil semua pengadaan untuk list.
     */
    public function getAll(?int $tahun = null): array
    {
        $query = Pengadaan::with(['jenisPengadaan:id,nama', 'status:id,nama', 'tahapans'])
            ->orderByDesc('id');

        if ($tahun) {
            $query->whereYear('tanggal_mulai', $tahun);
        }

        return $query->get()->map(fn ($p) => $this->transform($p))->toArray();
    }

    /**
     * Transform pengadaan untuk frontend.
     */
    public function transform(Pengadaan $p): array
    {
        return [
            'id' => $p->id,
            'kode' => $p->kode,
            'nama' => $p->nama,
            'jenis_pengadaan_id' => $p->jenis_pengadaan_id,
            'jenis' => $p->jenisPengadaan ? $p->jenisPengadaan->nama : '-',
            'status_id' => $p->status_id,
            'status' => $p->status ? $p->status->nama : '-',
            'progress' => $p->progress,
            'progress_otomatis' => $p->progress_otomatis,
            'nilai' => (float) $p->nilai,
            'penyedia' => $p->penyedia,
            'tanggal_mulai' => $p->tanggal_mulai?->format('Y-m-d'),
            'tanggal_selesai' => $p->tanggal_selesai?->format('Y-m-d'),
            'jumlah_tahapan' => $p->jumlah_tahapan,
            'jumlah_selesai' => $p->jumlah_selesai,
            'tahapans' => $p->tahapans->map(fn ($t) => $this->transformTahapan($t))->toArray(),
        ];
    }

    /**
     * Transform tahapan.
     */
    public function transformTahapan(PengadaanTahapan $t): array
    {
        return [
            'id' => $t->id,
            'nama' => $t->nama,
            'urutan' => $t->urutan,
            'selesai' => (bool) $t->selesai,
            'tanggal_selesai' => $t->tanggal_selesai?->format('Y-m-d'),
            'kendala' => $t->kendala,
        ];
    }

    /**
     * Create pengadaan baru.
     */
    public function create(array $data): Pengadaan
    {
        return DB::transaction(function () use ($data) {
            // Auto-set status
            $statusId = Status::where('nama', 'Draft')->value('id');

            $pengadaan = Pengadaan::create([
                'kode' => $data['kode'],
                'nama' => $data['nama'],
                'jenis_pengadaan_id' => $data['jenis_pengadaan_id'],
                'status_id' => $statusId,
                'progress' => 0,
                'nilai' => $data['nilai'],
                'penyedia' => $data['penyedia'] ?? null,
                'tanggal_mulai' => $data['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
            ]);

            // Tambah tahapan template kalau diminta
            if (!empty($data['use_template'])) {
                $template = $this->getTemplateTahapan();
                foreach ($template as $i => $nama) {
                    $pengadaan->tahapans()->create([
                        'nama' => $nama,
                        'urutan' => $i + 1,
                        'selesai' => false,
                    ]);
                }
            }

            return $pengadaan->fresh(['tahapans']);
        });
    }

    /**
     * Update pengadaan (tanpa tahapan).
     */
    public function update(Pengadaan $pengadaan, array $data): Pengadaan
    {
        return DB::transaction(function () use ($pengadaan, $data) {
            $pengadaan->update([
                'kode' => $data['kode'],
                'nama' => $data['nama'],
                'jenis_pengadaan_id' => $data['jenis_pengadaan_id'],
                'nilai' => $data['nilai'],
                'penyedia' => $data['penyedia'] ?? null,
                'tanggal_mulai' => $data['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
            ]);

            return $pengadaan->fresh(['tahapans']);
        });
    }

    /**
     * Delete pengadaan.
     */
    public function delete(Pengadaan $pengadaan): void
    {
        DB::transaction(function () use ($pengadaan) {
            $pengadaan->tahapans()->delete();
            $pengadaan->delete();
        });
    }

    /**
     * Tambah tahapan baru.
     */
    public function addTahapan(Pengadaan $pengadaan, array $data): PengadaanTahapan
    {
        $maxUrutan = $pengadaan->tahapans()->max('urutan') ?? 0;

        $tahapan = $pengadaan->tahapans()->create([
            'nama' => $data['nama'],
            'urutan' => $maxUrutan + 1,
            'selesai' => false,
        ]);

        $pengadaan->refresh()->recalculate();

        return $tahapan;
    }

    /**
     * Update tahapan.
     */
    public function updateTahapan(PengadaanTahapan $tahapan, array $data): PengadaanTahapan
    {
        $tahapan->update([
            'nama' => $data['nama'],
            'selesai' => $data['selesai'] ?? $tahapan->selesai,
            'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
            'kendala' => $data['kendala'] ?? null,
        ]);

        // Auto-set tanggal_selesai kalau centang selesai & tanggal kosong
        if ($tahapan->selesai && !$tahapan->tanggal_selesai) {
            $tahapan->update(['tanggal_selesai' => now()->format('Y-m-d')]);
        }

        $tahapan->pengadaan->refresh()->recalculate();

        return $tahapan->fresh();
    }

    /**
     * Toggle selesai tahapan.
     */
    public function toggleTahapan(PengadaanTahapan $tahapan): PengadaanTahapan
    {
        $newStatus = !$tahapan->selesai;

        $tahapan->update([
            'selesai' => $newStatus,
            'tanggal_selesai' => $newStatus ? ($tahapan->tanggal_selesai ?? now()->format('Y-m-d')) : null,
        ]);

        $tahapan->pengadaan->refresh()->recalculate();

        return $tahapan->fresh();
    }

    /**
     * Hapus tahapan.
     */
    public function deleteTahapan(PengadaanTahapan $tahapan): void
    {
        $pengadaan = $tahapan->pengadaan;
        $tahapan->delete();

        // Reorder sisa tahapan
        $pengadaan->tahapans()->orderBy('urutan')->get()->each(function ($t, $i) {
            $t->update(['urutan' => $i + 1]);
        });

        $pengadaan->refresh()->recalculate();
    }

    /**
     * Reorder tahapan (naik/turun).
     */
    public function reorderTahapan(PengadaanTahapan $tahapan, string $direction): void
    {
        $pengadaan = $tahapan->pengadaan;

        $operator = $direction === 'up' ? '<' : '>';
        $order = $direction === 'up' ? 'desc' : 'asc';

        $swap = $pengadaan->tahapans()
            ->where('urutan', $operator, $tahapan->urutan)
            ->orderBy('urutan', $order)
            ->first();

        if (!$swap) return;

        DB::transaction(function () use ($tahapan, $swap) {
            $temp = $tahapan->urutan;
            $tahapan->update(['urutan' => $swap->urutan]);
            $swap->update(['urutan' => $temp]);
        });
    }
}
