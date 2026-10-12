<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePnbpDetailRequest;
use App\Http\Requests\StorePnbpRequest;
use App\Models\Pnbp;
use App\Models\PnbpDetail;
use App\Services\PnbpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PnbpController extends Controller
{
    public function __construct(
        protected PnbpService $service
    ) {}

    /**
     * Halaman kelola PNBP.
     */
    public function index(Request $request): View
    {
        $tahun = (int) $request->get('tahun', date('Y'));
        if ($tahun < 2020 || $tahun > 2099) {
            $tahun = (int) date('Y');
        }

        return view('keuangan.pnbp', [
            'tahun' => $tahun,
            'availableYears' => $this->service->getAvailableYears(),
        ]);
    }

    /**
     * Data PNBP per tahun (AJAX).
     */
    public function data(Request $request): JsonResponse
    {
        $tahun = (int) $request->get('tahun', date('Y'));

        $data = $this->service->getByYear($tahun);

        return response()->json([
            'ok' => true,
            'tahun' => $tahun,
            'exists' => $data !== null,
            'pnbp' => $data,
            'kategoris' => $this->service->getKategoris(),
            'available_years' => $this->service->getAvailableYears(),
        ]);
    }

    /**
     * Buat PNBP baru (tahun baru).
     */
    public function store(StorePnbpRequest $request): JsonResponse
    {
        $pnbp = $this->service->create($request->validated());

        return response()->json([
            'ok' => true,
            'message' => 'PNBP tahun ' . $pnbp->tahun . ' berhasil dibuat.',
            'pnbp' => $this->service->transform($pnbp),
        ]);
    }

    /**
     * Update detail PNBP.
     */
    public function updateDetail(StorePnbpDetailRequest $request, int $detailId): JsonResponse
    {
        $detail = $this->service->updateDetail($detailId, $request->validated());

        return response()->json([
            'ok' => true,
            'message' => 'Detail PNBP berhasil diupdate.',
        ]);
    }

    public function destroyDetail(int $detailId): JsonResponse
    {
        $this->service->deleteDetail($detailId);

        return response()->json([
            'ok' => true,
            'message' => 'Detail berhasil dihapus.',
        ]);
    }

    /**
     * Hapus PNBP (per tahun).
     */
    public function destroy(int $id): JsonResponse
    {
        $pnbp = Pnbp::findOrFail($id);
        $this->service->delete($pnbp);

        return response()->json([
            'ok' => true,
            'message' => 'PNBP tahun ' . $pnbp->tahun . ' berhasil dihapus.',
        ]);
    }
}
