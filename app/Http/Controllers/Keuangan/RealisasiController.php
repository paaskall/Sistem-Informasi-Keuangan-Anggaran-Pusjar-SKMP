<?php

namespace App\Http\Controllers\Keuangan;

use App\Helpers\TahunHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRealisasiRequest;
use App\Models\Realisasi;
use App\Models\Target;
use App\Services\RealisasiService;
use App\Services\TargetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RealisasiController extends Controller
{
    public function __construct(
        protected RealisasiService $service,
        protected TargetService $targetService
    ) {}

    /**
     * Halaman list realisasi.
     */
    public function index(Request $request): View
    {
        $tahun = (int) $request->get('tahun', date('Y'));
        if ($tahun < 2020 || $tahun > 2099) {
            $tahun = (int) date('Y');
        }

        return view('keuangan.realisasi', [
            'tahun' => $tahun,
            'tahunOptions' => [2026, 2025, 2024],
            'tahunBerjalan' => TahunHelper::tahunBerjalan(),
        ]);
    }

    /**
     * Data realisasi per tahun.
     */
    public function data(Request $request): JsonResponse
    {
        $tahun = (int) $request->get('tahun', date('Y'));

        // Data target untuk perbandingan
        $targets = Target::where('tahun', $tahun)->get()->keyBy('id');

        $realisasis = $this->service->getRealisasisByYear($tahun);

        // Enrich dengan data target (untuk capaian per komponen)
        $realisasis = collect($realisasis)->map(function ($r) use ($targets) {
            $target = $targets->get($r['target_id']);
            $r['target_total'] = $target ? $target->total_nominal : 0;
            $r['capaian'] = $r['target_total'] > 0
                ? (int) round(($r['nominal'] / $r['target_total']) * 100)
                : 0;
            return $r;
        })->toArray();

        return response()->json([
            'ok' => true,
            'tahun' => $tahun,
            'editable' => TahunHelper::isEditable($tahun),
            'tahun_berjalan' => TahunHelper::tahunBerjalan(),
            'realisasis' => $realisasis,
            'total_target' => $this->targetService->getTotalTahunan($tahun),
            'total_realisasi' => $this->service->getTotalTahunan($tahun),
            'available_targets' => $this->service->getAvailableTargets($tahun),
            'kategoris' => \App\Models\KategoriKendala::orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    /**
     * Simpan realisasi baru.
     */
    public function store(StoreRealisasiRequest $request): JsonResponse
    {
        $target = Target::findOrFail($request->target_id);

        //   Cek tahun arsip
        if (!TahunHelper::isEditable((int) $target->tahun)) {
            return response()->json([
                'ok' => false,
                'message' => 'Tahun ' . $target->tahun . ' sudah dikunci (arsip). Data historis tidak bisa diubah.',
            ], 403);
        }

        $realisasi = $this->service->createRealisasi(
            $request->validated(),
            auth()->id()
        );

        return response()->json([
            'ok' => true,
            'message' => 'Realisasi berhasil disimpan.',
            'realisasi' => $this->service->transformRealisasi(
                $realisasi->load('details.program', 'details.subKegiatan', 'details.komponenBelanja', 'kendalas')
            ),
        ]);
    }

    /**
     * Update realisasi.
     */
    public function update(StoreRealisasiRequest $request, int $id): JsonResponse
    {
        $realisasi = Realisasi::findOrFail($id);

        //   Cek tahun arsip
        if (!TahunHelper::isEditable((int) $realisasi->tahun)) {
            return response()->json([
                'ok' => false,
                'message' => 'Tahun ' . $realisasi->tahun . ' sudah dikunci (arsip). Data historis tidak bisa diubah.',
            ], 403);
        }

        // Cek target baru (kalau pindah target)
        $newTarget = Target::findOrFail($request->target_id);
        if (!TahunHelper::isEditable((int) $newTarget->tahun)) {
            return response()->json([
                'ok' => false,
                'message' => 'Tidak bisa memindahkan realisasi ke tahun ' . $newTarget->tahun . ' (arsip).',
            ], 403);
        }

        $realisasi = $this->service->updateRealisasi($realisasi, $request->validated());

        return response()->json([
            'ok' => true,
            'message' => 'Realisasi berhasil diupdate.',
            'realisasi' => $this->service->transformRealisasi(
                $realisasi->load('details.program', 'details.subKegiatan', 'details.komponenBelanja', 'kendalas')
            ),
        ]);
    }

    /**
     * Hapus realisasi (soft delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $realisasi = Realisasi::findOrFail($id);

        //   Cek tahun arsip
        if (!TahunHelper::isEditable((int) $realisasi->tahun)) {
            return response()->json([
                'ok' => false,
                'message' => 'Tahun ' . $realisasi->tahun . ' sudah dikunci (arsip). Data historis tidak bisa dihapus.',
            ], 403);
        }

        $this->service->deleteRealisasi($realisasi);

        return response()->json([
            'ok' => true,
            'message' => 'Realisasi berhasil dihapus.',
        ]);
    }

    /**
     * Ambil detail target (untuk pre-fill form realisasi).
     */
    public function targetDetails(int $targetId): JsonResponse
    {
        $target = $this->service->getTargetWithDetails($targetId);

        if (!$target) {
            return response()->json([
                'ok' => false,
                'message' => 'Target tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'target' => $target,
        ]);
    }

    /**
     * Endpoint reminder untuk halaman realisasi.
     */
    public function reminders(\App\Services\ReminderService $service): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'reminders' => $service->getReminders(),
        ]);
    }
}