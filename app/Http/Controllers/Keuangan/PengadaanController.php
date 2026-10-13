<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengadaanRequest;
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

    public function index(): View
    {
        return view('keuangan.pengadaan');
    }

    public function detail(int $id): View
    {
        $pengadaan = Pengadaan::with(['jenisPengadaan', 'status', 'tahapans'])->findOrFail($id);

        return view('keuangan.pengadaan-detail', [
            'pengadaanId' => $pengadaan->id,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $tahun = $request->filled('tahun') ? (int) $request->tahun : null;

        $pengadaans = $this->service->getAll($tahun);

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

    public function detailData(int $id): JsonResponse
    {
        $pengadaan = Pengadaan::with(['jenisPengadaan', 'status', 'tahapans'])->findOrFail($id);

        return response()->json([
            'ok' => true,
            'pengadaan' => $this->service->transform($pengadaan),
        ]);
    }

    public function store(StorePengadaanRequest $request): JsonResponse
    {
        $pengadaan = $this->service->create($request->validated());

        return response()->json([
            'ok' => true,
            'message' => 'Pengadaan berhasil ditambahkan.',
            'pengadaan' => $this->service->transform($pengadaan),
        ]);
    }

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

    public function storeTahapan(Request $request, int $pengadaanId): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kendala' => 'nullable|string',
            'dampak' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
            'selesai' => 'nullable|boolean',
        ]);

        $pengadaan = Pengadaan::findOrFail($pengadaanId);

        $urutan = ($pengadaan->tahapans()->max('urutan') ?? 0) + 1;

        $pengadaan->tahapans()->create([
            'nama' => $validated['nama'],
            'urutan' => $urutan,
            'kendala' => $validated['kendala'] ?? null,
            'dampak' => $validated['dampak'] ?? null,
            'tindak_lanjut' => $validated['tindak_lanjut'] ?? null,
            'selesai' => $validated['selesai'] ?? false,
            'tanggal_selesai' => ($validated['selesai'] ?? false) ? now() : null,
        ]);

        $pengadaan->recalculate();
        $pengadaan->refresh();

        return response()->json([
            'ok' => true,
            'message' => 'Tahapan berhasil ditambahkan',
            'pengadaan' => $this->service->transform($pengadaan),
        ]);
    }

    public function updateTahapan(Request $request, int $tahapanId): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kendala' => 'nullable|string',
            'dampak' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
            'selesai' => 'nullable|boolean',
        ]);

        $tahapan = PengadaanTahapan::findOrFail($tahapanId);

        $tahapan->update([
            'nama' => $validated['nama'],
            'kendala' => $validated['kendala'] ?? null,
            'dampak' => $validated['dampak'] ?? null,
            'tindak_lanjut' => $validated['tindak_lanjut'] ?? null,
            'selesai' => $validated['selesai'] ?? false,
            'tanggal_selesai' => ($validated['selesai'] ?? false) ? now() : null,
        ]);

        $pengadaan = $tahapan->pengadaan;
        $pengadaan->recalculate();
        $pengadaan->refresh();

        return response()->json([
            'ok' => true,
            'message' => 'Tahapan berhasil diupdate',
            'pengadaan' => $this->service->transform($pengadaan),
        ]);
    }

    public function toggleTahapan(int $tahapanId): JsonResponse
    {
        try {
            $tahapan = PengadaanTahapan::findOrFail($tahapanId);

            $tahapan->selesai = !$tahapan->selesai;
            $tahapan->tanggal_selesai = $tahapan->selesai ? now() : null;
            $tahapan->save();

            $pengadaan = $tahapan->pengadaan;
            $pengadaan->recalculate();
            $pengadaan->refresh();

            return response()->json([
                'ok' => true,
                'message' => $tahapan->selesai ? 'Tahapan ditandai selesai.' : 'Tahapan ditandai belum selesai.',
                'pengadaan' => $this->service->transform($pengadaan),
            ]);
        } catch (\Throwable $e) {
            \Log::error('[Toggle Tahapan] ' . $e->getMessage(), [
                'tahapan_id' => $tahapanId,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle kendala sudah diatasi / belum.
     */
    public function toggleKendala(int $tahapanId): JsonResponse
    {
        try {
            $tahapan = PengadaanTahapan::findOrFail($tahapanId);

            if (!$tahapan->kendala) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Tahapan ini tidak memiliki kendala.',
                ], 422);
            }

            $tahapan->kendala_selesai = !$tahapan->kendala_selesai;
            $tahapan->kendala_selesai_at = $tahapan->kendala_selesai ? now() : null;
            $tahapan->save();

            $pengadaan = $tahapan->pengadaan;
            $pengadaan->refresh();

            return response()->json([
                'ok' => true,
                'message' => $tahapan->kendala_selesai
                    ? 'Kendala ditandai sudah diatasi.'
                    : 'Kendala ditandai aktif kembali.',
                'pengadaan' => $this->service->transform($pengadaan),
            ]);
        } catch (\Throwable $e) {
            \Log::error('[Toggle Kendala] ' . $e->getMessage(), [
                'tahapan_id' => $tahapanId,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

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