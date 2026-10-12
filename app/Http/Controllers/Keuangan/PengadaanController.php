<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengadaanRequest;
use App\Http\Requests\StorePengadaanTahapanRequest;
use App\Models\JenisPengadaan;
use App\Models\Pengadaan;
use App\Models\PengadaanTahapan;
use App\Services\PengadaanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengadaanController extends Controller
{
    public function __construct(
        protected PengadaanService $service
    ) {}

    /**
     * Halaman list pengadaan.
     */
    public function index(): View
    {
        return view('keuangan.pengadaan');
    }

    /**
     * Halaman detail pengadaan.
     */
    public function detail(int $id): View
    {
        $pengadaan = Pengadaan::with(['jenisPengadaan', 'status', 'tahapans'])->findOrFail($id);

        return view('keuangan.pengadaan-detail', [
            'pengadaanId' => $pengadaan->id,
        ]);
    }

    /**
     * Data list pengadaan (AJAX).
     */
    public function data(Request $request): JsonResponse
    {
        $tahun = $request->filled('tahun') ? (int) $request->tahun : null;

        $pengadaans = $this->service->getAll($tahun);

        // Stats
        $all = Pengadaan::all();
        $stats = [
            'total' => $all->count(),
            'draft' => $all->where('progress', 0)->count(),
            'proses' => $all->whereBetween('progress', [1, 99])->count(),
            'selesai' => $all->where('progress', 100)->count(),
        ];

        return response()->json([
            'ok' => true,
            'pengadaans' => $pengadaans,
            'stats' => $stats,
            'next_kode' => $this->service->generateKode(),
            'jenis_list' => JenisPengadaan::orderBy('nama')->get(['id', 'nama']),
            'template_tahapan' => $this->service->getTemplateTahapan(),
        ]);
    }

    /**
     * Data detail pengadaan (AJAX).
     */
    public function detailData(int $id): JsonResponse
    {
        $pengadaan = Pengadaan::with(['jenisPengadaan', 'status', 'tahapans'])->findOrFail($id);

        return response()->json([
            'ok' => true,
            'pengadaan' => $this->service->transform($pengadaan),
        ]);
    }

    /**
     * Simpan pengadaan baru.
     */
    public function store(StorePengadaanRequest $request): JsonResponse
    {
        $pengadaan = $this->service->create($request->validated());

        return response()->json([
            'ok' => true,
            'message' => 'Pengadaan berhasil ditambahkan.',
            'pengadaan' => $this->service->transform($pengadaan),
        ]);
    }

    /**
     * Update pengadaan.
     */
    public function update(StorePengadaanRequest $request, int $id): JsonResponse
    {
        $pengadaan = Pengadaan::findOrFail($id);
        $pengadaan = $this->service->update($pengadaan, $request->validated());

        return response()->json([
            'ok' => true,
            'message' => 'Pengadaan berhasil diupdate.',
            'pengadaan' => $this->service->transform($pengadaan),
        ]);
    }

    /**
     * Hapus pengadaan.
     */
    public function destroy(int $id): JsonResponse
    {
        $pengadaan = Pengadaan::findOrFail($id);
        $this->service->delete($pengadaan);

        return response()->json([
            'ok' => true,
            'message' => 'Pengadaan berhasil dihapus.',
        ]);
    }

    // ============================================================
    // TAHAPAN
    // ============================================================

    /**
     * Tambah tahapan.
     */
    public function storeTahapan(StorePengadaanTahapanRequest $request, int $pengadaanId): JsonResponse
    {
        $pengadaan = Pengadaan::findOrFail($pengadaanId);
        $tahapan = $this->service->addTahapan($pengadaan, $request->validated());

        return response()->json([
            'ok' => true,
            'message' => 'Tahapan berhasil ditambahkan.',
            'tahapan' => $this->service->transformTahapan($tahapan),
            'pengadaan' => $this->service->transform($pengadaan->fresh(['tahapans', 'jenisPengadaan', 'status'])),
        ]);
    }

    /**
     * Update tahapan.
     */
    public function updateTahapan(StorePengadaanTahapanRequest $request, int $tahapanId): JsonResponse
    {
        $tahapan = PengadaanTahapan::findOrFail($tahapanId);
        $tahapan = $this->service->updateTahapan($tahapan, $request->validated());

        return response()->json([
            'ok' => true,
            'message' => 'Tahapan berhasil diupdate.',
            'tahapan' => $this->service->transformTahapan($tahapan),
            'pengadaan' => $this->service->transform($tahapan->pengadaan->fresh(['tahapans', 'jenisPengadaan', 'status'])),
        ]);
    }

    /**
     * Toggle selesai tahapan.
     */
    public function toggleTahapan(int $tahapanId): JsonResponse
    {
        $tahapan = PengadaanTahapan::findOrFail($tahapanId);
        $tahapan = $this->service->toggleTahapan($tahapan);

        return response()->json([
            'ok' => true,
            'message' => $tahapan->selesai ? 'Tahapan ditandai selesai.' : 'Tahapan ditandai belum selesai.',
            'tahapan' => $this->service->transformTahapan($tahapan),
            'pengadaan' => $this->service->transform($tahapan->pengadaan->fresh(['tahapans', 'jenisPengadaan', 'status'])),
        ]);
    }

    /**
     * Hapus tahapan.
     */
    public function destroyTahapan(int $tahapanId): JsonResponse
    {
        $tahapan = PengadaanTahapan::findOrFail($tahapanId);
        $pengadaan = $tahapan->pengadaan;

        $this->service->deleteTahapan($tahapan);

        return response()->json([
            'ok' => true,
            'message' => 'Tahapan berhasil dihapus.',
            'pengadaan' => $this->service->transform($pengadaan->fresh(['tahapans', 'jenisPengadaan', 'status'])),
        ]);
    }

    /**
     * Reorder tahapan (naik/turun).
     */
    public function reorderTahapan(Request $request, int $tahapanId): JsonResponse
    {
        $request->validate([
            'direction' => ['required', 'in:up,down'],
        ]);

        $tahapan = PengadaanTahapan::findOrFail($tahapanId);
        $pengadaan = $tahapan->pengadaan;

        $this->service->reorderTahapan($tahapan, $request->direction);

        return response()->json([
            'ok' => true,
            'message' => 'Urutan tahapan berhasil diubah.',
            'pengadaan' => $this->service->transform($pengadaan->fresh(['tahapans', 'jenisPengadaan', 'status'])),
        ]);
    }
}
