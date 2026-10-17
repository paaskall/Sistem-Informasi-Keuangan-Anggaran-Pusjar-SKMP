<?php

namespace App\Services;

use App\Models\Kendala;
use App\Models\KomponenBelanja;
use App\Models\Program;
use App\Models\Realisasi;
use App\Models\RealisasiDetail;
use App\Models\Target;
use App\Models\TargetDetail;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getStats(int $tahun): array
    {
        $totalTarget = (float) TargetDetail::whereHas('target', fn ($q) => $q->where('tahun', $tahun))
            ->sum('nominal');

        $totalRealisasi = (float) Realisasi::where('tahun', $tahun)->sum('nominal');

        $capaian = $totalTarget > 0 ? (int) round(($totalRealisasi / $totalTarget) * 100) : 0;
        $selisih = $totalTarget - $totalRealisasi;

        $kendalaAktif = Kendala::where('status', 'Proses')->count();

        return [
            'total_target' => $totalTarget,
            'total_realisasi' => $totalRealisasi,
            'capaian' => $capaian,
            'selisih' => $selisih,
            'kendala_aktif' => $kendalaAktif,
        ];
    }

    public function getPerBulan(int $tahun): array
    {
        $targets = Target::with('details')
            ->where('tahun', $tahun)
            ->orderBy('bulan')
            ->get();

        $realisasis = Realisasi::where('tahun', $tahun)->get()->groupBy('bulan');

        return $targets->map(function ($t) use ($realisasis) {
            $realBulan = $realisasis->get($t->bulan, collect())->sum('nominal');
            return [
                'bulan' => $t->bulan,
                'bulan_nama' => $t->bulan_nama,
                'bulan_short' => $t->bulan_short,
                'target' => (float) $t->total_nominal,
                'realisasi' => (float) $realBulan,
                'capaian' => $t->total_nominal > 0
                    ? (int) round(($realBulan / $t->total_nominal) * 100)
                    : 0,
            ];
        })->values()->toArray();
    }

    public function getCapaianPerProgram(int $tahun): array
    {
        $targetMap = TargetDetail::select('program_id', DB::raw('SUM(nominal) as total'))
            ->whereHas('target', fn ($q) => $q->where('tahun', $tahun))
            ->groupBy('program_id')
            ->pluck('total', 'program_id')
            ->toArray();

        $realisasiMap = RealisasiDetail::select('program_id', DB::raw('SUM(nominal) as total'))
            ->whereHas('realisasi', fn ($q) => $q->where('tahun', $tahun))
            ->groupBy('program_id')
            ->pluck('total', 'program_id')
            ->toArray();

        $programs = Program::whereIn('id', array_unique(array_merge(array_keys($targetMap), array_keys($realisasiMap))))
            ->get();

        return $programs->map(function ($p) use ($targetMap, $realisasiMap) {
            $target = (float) ($targetMap[$p->id] ?? 0);
            $real = (float) ($realisasiMap[$p->id] ?? 0);
            $capaian = $target > 0 ? (int) round(($real / $target) * 100) : 0;

            return [
                'program_id' => $p->id,
                'kode' => $p->kode,
                'nama' => $p->nama,
                'target' => $target,
                'realisasi' => $real,
                'capaian' => $capaian,
            ];
        })
        ->filter(fn ($x) => $x['target'] > 0 || $x['realisasi'] > 0)
        ->sortByDesc('target')
        ->values()
        ->toArray();
    }

    public function getCapaianPerKomponen(int $tahun): array
    {
        $targetMap = TargetDetail::select('komponen_belanja_id', DB::raw('SUM(nominal) as total'))
            ->whereHas('target', fn ($q) => $q->where('tahun', $tahun))
            ->groupBy('komponen_belanja_id')
            ->pluck('total', 'komponen_belanja_id')
            ->toArray();

        $realisasiMap = RealisasiDetail::select('komponen_belanja_id', DB::raw('SUM(nominal) as total'))
            ->whereHas('realisasi', fn ($q) => $q->where('tahun', $tahun))
            ->groupBy('komponen_belanja_id')
            ->pluck('total', 'komponen_belanja_id')
            ->toArray();

        $komponens = KomponenBelanja::whereIn('id', array_unique(array_merge(array_keys($targetMap), array_keys($realisasiMap))))
            ->get();

        return $komponens->map(function ($k) use ($targetMap, $realisasiMap) {
            $target = (float) ($targetMap[$k->id] ?? 0);
            $real = (float) ($realisasiMap[$k->id] ?? 0);
            $capaian = $target > 0 ? (int) round(($real / $target) * 100) : 0;

            return [
                'komponen_id' => $k->id,
                'kode' => $k->kode,
                'nama' => $k->nama,
                'target' => $target,
                'realisasi' => $real,
                'capaian' => $capaian,
            ];
        })
        ->filter(fn ($x) => $x['target'] > 0 || $x['realisasi'] > 0)
        ->sortByDesc('target')
        ->values()
        ->toArray();
    }

    public function getBulanIni(int $tahun): ?array
    {
        $bulanIni = (int) date('n');
        $target = Target::where('tahun', $tahun)->where('bulan', $bulanIni)->first();

        if (!$target) return null;

        $real = Realisasi::where('target_id', $target->id)->sum('nominal');

        return [
            'bulan' => $bulanIni,
            'bulan_nama' => $target->bulan_nama,
            'target' => (float) $target->total_nominal,
            'realisasi' => (float) $real,
            'capaian' => $target->total_nominal > 0
                ? (int) round(($real / $target->total_nominal) * 100)
                : 0,
        ];
    }

    public function getRingkasanModul(): array
    {
        return [
            'total_program' => Program::count(),
            'total_sub_kegiatan' => \App\Models\SubKegiatan::count(),
            'total_komponen' => KomponenBelanja::count(),
            'total_target_bulanan' => Target::count(),
            'total_realisasi' => Realisasi::count(),
            'total_kendala' => Kendala::count(),
            'kendala_aktif' => Kendala::where('status', 'Proses')->count(),
        ];
    }

    public function getRencanaRealisasiBulanIni(int $tahun, ?int $bulan = null): array
    {
        $bulan = $bulan ?? (int) date('n');

        $target = Target::with([
            'details.komponenBelanja:id,kode,nama',
        ])
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->first();

        if (!$target) {
            return [
                'bulan' => $bulan,
                'bulan_nama' => $this->bulanNama($bulan),
                'tahun' => $tahun,
                'items' => [],
                'total_target' => 0,
                'total_realisasi' => 0,
                'status_summary' => ['siap' => 0, 'menunggu' => 0, 'belum' => 0],
            ];
        }

        $komponenTarget = [];
        foreach ($target->details as $d) {
            $kid = $d->komponen_belanja_id;
            if (!isset($komponenTarget[$kid])) {
                $komponenTarget[$kid] = [
                    'komponen_id' => $kid,
                    'kode' => $d->komponenBelanja?->kode ?? '???',
                    'nama' => $d->komponenBelanja?->nama ?? 'Tidak diketahui',
                    'target' => 0,
                    'realisasi' => 0,
                ];
            }
            $komponenTarget[$kid]['target'] += (float) $d->nominal;
        }

        $realisasiDetails = RealisasiDetail::whereHas('realisasi', function ($q) use ($tahun, $bulan, $target) {
            $q->where('tahun', $tahun)->where('bulan', $bulan)->where('target_id', $target->id);
        })->get();

        foreach ($realisasiDetails as $rd) {
            $kid = $rd->komponen_belanja_id;
            if (isset($komponenTarget[$kid])) {
                $komponenTarget[$kid]['realisasi'] += (float) $rd->nominal;
            }
        }

        $items = [];
        $totalTarget = 0;
        $totalRealisasi = 0;
        $summary = ['siap' => 0, 'menunggu' => 0, 'belum' => 0];

        foreach ($komponenTarget as $item) {
            $tgt = $item['target'];
            $real = $item['realisasi'];
            $pct = $tgt > 0 ? round(($real / $tgt) * 100) : 0;

            if ($real >= $tgt && $tgt > 0) {
                $status = 'siap';
                $statusLabel = 'Siap Dicairkan';
            } elseif ($real > 0) {
                $status = 'menunggu';
                $statusLabel = 'Masih Menunggu';
            } else {
                $status = 'belum';
                $statusLabel = 'Belum Diinput';
            }

            $summary[$status]++;

            $items[] = [
                'komponen_id' => $item['komponen_id'],
                'kode' => $item['kode'],
                'nama' => $item['nama'],
                'target' => $tgt,
                'realisasi' => $real,
                'selisih' => $tgt - $real,
                'capaian' => $pct,
                'status' => $status,
                'status_label' => $statusLabel,
            ];

            $totalTarget += $tgt;
            $totalRealisasi += $real;
        }

        usort($items, function ($a, $b) {
            $order = ['siap' => 1, 'menunggu' => 2, 'belum' => 3];
            if ($order[$a['status']] !== $order[$b['status']]) {
                return $order[$a['status']] - $order[$b['status']];
            }
            return $b['target'] <=> $a['target'];
        });

        return [
            'bulan' => $bulan,
            'bulan_nama' => $this->bulanNama($bulan),
            'tahun' => $tahun,
            'items' => $items,
            'total_target' => $totalTarget,
            'total_realisasi' => $totalRealisasi,
            'status_summary' => $summary,
        ];
    }

    /**
     * Progress pengadaan untuk donut chart.
     */
    public function getProgressPengadaan(int $limit = 3): array
    {
        $pengadaans = \App\Models\Pengadaan::with(['tahapans', 'jenisPengadaan'])
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return $pengadaans->map(function ($p) {
            $progress = $p->progress_otomatis;
            $status = 'Belum dimulai';
            if ($progress === 100) {
                $status = 'Selesai';
            } elseif ($progress > 0) {
                $status = 'Pelaksanaan';
            }

            $jenis = $p->jenisPengadaan?->nama ?? '-';

            return [
                'id' => $p->id,
                'kode' => $p->kode,
                'nama' => $p->nama,
                'jenis' => $jenis,
                'nilai' => (float) $p->nilai,
                'progress' => $progress,
                'status' => $status,
                'keterangan' => $status . ' ' . $jenis,
            ];
        })->toArray();
    }

    protected function bulanNama(int $bulan): string
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ][$bulan] ?? '-';
    }

    /**
     * Ringkasan PNBP untuk dashboard.
     */
    public function getPnbpSummary(int $tahun): array
    {
        $pnbp = \App\Models\Pnbp::with([
            'details.kategori:id,kode,nama',
        ])
            ->where('tahun', $tahun)
            ->first();

        if (!$pnbp) {
            return [
                'exists' => false,
                'tahun' => $tahun,
                'total_target' => 0,
                'total_realisasi' => 0,
                'capaian' => 0,
                'kategoris' => [],
            ];
        }

        // Group per kategori (parent only)
        $perKategori = [];
        $parents = $pnbp->details->whereNull('parent_id');

        foreach ($parents as $d) {
            $kid = $d->kategori_pnbp_id;
            if (!isset($perKategori[$kid])) {
                $perKategori[$kid] = [
                    'kategori_id' => $kid,
                    'kode' => $d->kategori?->kode ?? '-',
                    'nama' => $d->kategori?->nama ?? '-',
                    'target' => 0,
                    'realisasi' => 0,
                ];
            }
            $perKategori[$kid]['target'] += (float) $d->target;
            $perKategori[$kid]['realisasi'] += (float) $d->realisasi;
        }

        // Hitung capaian per kategori
        $kategoriList = collect($perKategori)->map(function ($k) {
            $k['capaian'] = $k['target'] > 0 ? (int) round(($k['realisasi'] / $k['target']) * 100) : 0;
            return $k;
        })->sortByDesc('target')->values()->toArray();

        return [
            'exists' => true,
            'tahun' => $pnbp->tahun,
            'unit' => $pnbp->unit,
            'total_target' => $pnbp->total_target,
            'total_realisasi' => $pnbp->total_realisasi,
            'capaian' => $pnbp->capaian,
            'kategoris' => $kategoriList,
        ];
    }

    // ============================================================
    // SECTION BARU: CATATAN UMPAN BALIK KENDALA
    // ============================================================

    /**
     * Matriks Manajemen Risiko Realisasi Anggaran
     * Ambil kendala aktif (status='Proses') untuk tahun tertentu
     * Data dampak & tindak_lanjut diambil dari DB
     */
    public function getCatatanKendala(int $tahun): array
    {
        $kendalas = Kendala::query()
            ->with(['kategoriKendala:id,nama'])
            ->proses()
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->limit(10)
            ->get();

        $items = [];
        foreach ($kendalas as $k) {
            $items[] = [
                'id' => $k->id,
                'kendala' => $k->deskripsi,
                'kategori' => $k->kategoriKendala?->nama ?? 'Umum',
                'dampak' => $k->dampak,
                'tindak_lanjut' => $k->tindak_lanjut,
            ];
        }

        if (empty($items)) {
            $items = [
                [
                    'id' => 0,
                    'kendala' => 'Kelengkapan dokumen pembayaran belum terpenuhi',
                    'kategori' => 'Administrasi',
                    'dampak' => 'Pencairan anggaran tertunda dan realisasi bulanan tidak sesuai rencana.',
                    'tindak_lanjut' => 'Memeriksa kelengkapan dokumen sebelum pengajuan, menetapkan PIC dan batas waktu penyelesaian, serta berkoordinasi dengan pengelola keuangan dan KPPN.',
                ],
                [
                    'id' => 0,
                    'kendala' => 'Pengadaan barang/jasa belum selesai sesuai jadwal',
                    'kategori' => 'Pengadaan',
                    'dampak' => 'Pembayaran dan pemanfaatan barang/jasa tertunda sehingga penyerapan anggaran melambat.',
                    'tindak_lanjut' => 'Mempercepat proses pengadaan, monitoring jadwal, dan tindak lanjut dengan penyedia.',
                ],
            ];
        }

        return [
            'title' => 'Matriks Manajemen Risiko Realisasi Anggaran',
            'subtitle' => 'Identifikasi kendala/risiko, dampak, dan mitigasi',
            'items' => $items,
        ];
    }

    // ============================================================
    // SECTION BARU: RENCANA PENGADAAN
    // ============================================================

    /**
     * Rencana Pengadaan
     * Ambil 5 paket pengadaan terbaru untuk tahun tertentu
     */
    public function getRencanaPengadaan(int $tahun): array
    {
        $pengadaans = \App\Models\Pengadaan::query()
            ->with(['jenisPengadaan:id,nama'])
            ->whereYear('tanggal_mulai', $tahun)
            ->orderBy('tanggal_mulai', 'asc')
            ->limit(5)
            ->get();

        $items = [];
        foreach ($pengadaans as $p) {
            // Format waktu
            $waktu = 'Tanggal pelaksanaan belum ditentukan';
            if ($p->tanggal_mulai && $p->tanggal_selesai) {
                $waktu = $p->tanggal_mulai->format('d M Y')
                       . ' s.d '
                       . $p->tanggal_selesai->format('d M Y');
            } elseif ($p->tanggal_mulai) {
                $waktu = 'Mulai: ' . $p->tanggal_mulai->format('d M Y');
            }

            // Keterangan progress
            $keterangan = 'Belum dimulai';
            if ($p->progress >= 100) {
                $keterangan = 'Sudah selesai';
            } elseif ($p->progress > 0) {
                $keterangan = 'Pelaksanaan';
            }

            $items[] = [
                'kode' => $p->kode,
                'nama' => $p->nama,
                'jenis' => $p->jenisPengadaan?->nama ?? '-',
                'anggaran' => (float) $p->nilai,
                'waktu' => $waktu,
                'progress' => (int) $p->progress,
                'keterangan' => $keterangan,
            ];
        }

        return [
            'title' => 'Rencana Pengadaan',
            'items' => $items,
        ];
    }

    // ============================================================
    // SECTION BARU: AKSELERASI PENYERAPAN ANGGARAN
    // ============================================================

    /**
     * Tabel Akselerasi Penyerapan Anggaran
     * Ambil kegiatan percepatan yang masih berjalan
     */
    public function getAkselerasi(int $tahun): array
    {
        $items = \App\Models\AkselerasiPenyerapan::query()
            ->terurut()
            ->limit(10)
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'kegiatan' => $a->kegiatan,
                    'jangka_waktu' => $a->jangka_waktu,
                    'progress' => (int) $a->progress,
                    'keterangan' => $a->keterangan,
                ];
            })
            ->toArray();

        // Fallback: kalau tidak ada data, pakai contoh default
        if (empty($items)) {
            $items = [
                [
                    'id' => 0,
                    'kegiatan' => 'Percepatan penyelesaian dokumen pencairan anggaran',
                    'jangka_waktu' => 'Oktober 2026',
                    'progress' => 75,
                    'keterangan' => 'Koordinasi kelengkapan dokumen dengan unit terkait',
                ],
                [
                    'id' => 0,
                    'kegiatan' => 'Percepatan proses pengadaan barang/jasa',
                    'jangka_waktu' => 'Oktober–November 2026',
                    'progress' => 60,
                    'keterangan' => 'Pemantauan jadwal dan tindak lanjut dengan penyedia',
                ],
                [
                    'id' => 0,
                    'kegiatan' => 'Monitoring realisasi anggaran setiap minggu',
                    'jangka_waktu' => 'Minggu s.d Desember 2026',
                    'progress' => 50,
                    'keterangan' => 'Evaluasi deviasi target dan tindak lanjut kendala',
                ],
            ];
        }

        return [
            'title' => 'Tabel Akselerasi Penyerapan Anggaran',
            'subtitle' => 'Kegiatan percepatan pencapaian target anggaran',
            'items' => $items,
        ];
    }

    // ============================================================
    // KENDALA DALAM PENGADAAN
    // ============================================================

    /**
     * Kendala dalam Pengadaan
     * Ambil kendala dari tahapan pengadaan yang belum selesai
     * Data dampak & tindak_lanjut diambil dari DB
     */
    public function getKendalaPengadaan(int $tahun): array
    {
        $tahapans = \App\Models\PengadaanTahapan::query()
            ->with(['pengadaan:id,kode,nama,jenis_pengadaan_id'])
            ->whereNotNull('kendala')
            ->where('kendala', '!=', '')
            ->where('selesai', false)
            ->where('kendala_selesai', false)                  // ⬅️ TAMBAH — hanya kendala AKTIF
            ->whereHas('pengadaan', function ($q) use ($tahun) {
                $q->whereYear('tanggal_mulai', $tahun);
            })
            ->orderBy('pengadaan_id')
            ->orderBy('urutan')
            ->limit(10)
            ->get();

        $items = [];
        foreach ($tahapans as $t) {
            $items[] = [
                'id' => $t->id,
                'kategori' => $t->nama,
                'kendala' => $t->kendala,
                'dampak' => $t->dampak,
                'tindak_lanjut' => $t->tindak_lanjut,
                'pengadaan_kode' => $t->pengadaan?->kode ?? '-',
            ];
        }

        return [
            'title' => 'Kendala dalam Pengadaan',
            'subtitle' => 'Kendala tahapan pengadaan, dampak, dan tindak lanjut',
            'items' => $items,
        ];
    }

    // ============================================================
    // WIDGET: UPCOMING TASKS
    // ============================================================

    /**
     * Ambil task mendatang untuk widget dashboard.
     * Return: array task terdekat (belum selesai).
     */
    public function getUpcomingTasks(int $limit = 5): array
    {
        $tasks = \App\Models\Task::upcoming($limit)
            ->with(['assignedUser:id,name'])
            ->get();

        return $tasks->map(function ($t) {
            return [
                'id' => $t->id,
                'judul' => $t->judul,
                'status' => $t->status,
                'status_label' => $t->status_label,
                'status_color' => $t->status_color,
                'tanggal_mulai' => $t->tanggal_mulai?->format('Y-m-d'),
                'tanggal_mulai_formatted' => $t->tanggal_mulai?->translatedFormat('d M Y'),
                'tanggal_selesai_formatted' => $t->tanggal_selesai?->translatedFormat('d M Y'),
                'assigned_name' => $t->assignedUser?->name,
                'is_overdue' => $t->is_overdue,
                'hari_tersisa' => $t->hari_tersisa,
            ];
        })->toArray();
    }
}