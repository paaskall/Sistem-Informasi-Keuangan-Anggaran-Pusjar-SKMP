<?php

namespace App\Http\Controllers\Keuangan;

use App\Helpers\TahunHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTargetRequest;
use App\Models\Target;
use App\Services\TargetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TargetController extends Controller
{
    public function __construct(
        protected TargetService $service
    ) {}

    /**
     * Halaman kelola target.
     */
    public function index(Request $request): View
    {
        $tahun = (int) $request->get('tahun', date('Y'));
        if ($tahun < 2020 || $tahun > 2099) {
            $tahun = (int) date('Y');
        }

        return view('keuangan.target', [
            'tahun' => $tahun,
            'tahunOptions' => [2026, 2025, 2024],
            'tahunBerjalan' => TahunHelper::tahunBerjalan(),
        ]);
    }

    /**
     * Data target per tahun.
     */
    public function data(Request $request): JsonResponse
    {
        $tahun = (int) $request->get('tahun', date('Y'));

        return response()->json([
            'ok' => true,
            'tahun' => $tahun,
            'targets' => $this->service->getTargetsByYear($tahun),
            'total_tahunan' => $this->service->getTotalTahunan($tahun),
            'references' => $this->service->getReferenceData(),
            'editable' => TahunHelper::isEditable($tahun),
            'tahun_berjalan' => TahunHelper::tahunBerjalan(),
        ]);
    }

    /**
     * Simpan target baru.
     */
    public function store(StoreTargetRequest $request): JsonResponse
    {
        // ⭐ Cek tahun arsip
        if (!TahunHelper::isEditable((int) $request->tahun)) {
            return response()->json([
                'ok' => false,
                'message' => 'Tahun ' . $request->tahun . ' sudah dikunci (arsip). Data historis tidak bisa diubah.',
            ], 403);
        }

        // ⭐ Handle error duplicate
        try {
            $target = $this->service->createTarget($request->validated());

            return response()->json([
                'ok' => true,
                'message' => 'Target berhasil ditambahkan.',
                'target' => $this->service->transformTarget(
                    $target->load('details.program', 'details.subKegiatan', 'details.komponenBelanja')
                ),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Cek apakah error duplicate
            if ($e->getCode() === '23000') {
                $bulanNama = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                ][$request->bulan] ?? $request->bulan;

                return response()->json([
                    'ok' => false,
                    'message' => "Target untuk {$bulanNama} {$request->tahun} sudah ada. Silakan edit target yang sudah ada.",
                ], 422);
            }

            // Error lain
            return response()->json([
                'ok' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update target.
     */
    public function update(StoreTargetRequest $request, int $id): JsonResponse
    {
        $target = Target::findOrFail($id);

        // Guard tahun arsip
        if (!TahunHelper::isEditable((int) $target->tahun)) {
            return response()->json([
                'ok' => false,
                'message' => 'Tahun ' . $target->tahun . ' sudah dikunci (arsip).',
            ], 403);
        }

        if (!TahunHelper::isEditable((int) $request->tahun)) {
            return response()->json([
                'ok' => false,
                'message' => 'Tidak bisa memindahkan data ke tahun ' . $request->tahun . ' (arsip).',
            ], 403);
        }

        try {
            $target = $this->service->updateTarget($target, $request->validated());

            return response()->json([
                'ok' => true,
                'message' => 'Target berhasil diupdate.',
                'target' => $this->service->transformTarget(
                    $target->load('details.program', 'details.subKegiatan', 'details.komponenBelanja')
                ),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                $bulanNama = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                ][$request->bulan] ?? $request->bulan;

                return response()->json([
                    'ok' => false,
                    'message' => "Target untuk {$bulanNama} {$request->tahun} sudah ada.",
                ], 422);
            }

            return response()->json([
                'ok' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus target (soft delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $target = Target::findOrFail($id);

        //  Cek tahun arsip
        if (!TahunHelper::isEditable((int) $target->tahun)) {
            return response()->json([
                'ok' => false,
                'message' => 'Tahun ' . $target->tahun . ' sudah dikunci (arsip). Data historis tidak bisa dihapus.',
            ], 403);
        }

        $this->service->deleteTarget($target);

        return response()->json([
            'ok' => true,
            'message' => 'Target berhasil dihapus.',
        ]);
    }
}