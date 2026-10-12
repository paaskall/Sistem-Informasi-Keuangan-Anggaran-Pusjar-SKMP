<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Pnbp;
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
     * Halaman PNBP pimpinan (read-only).
     */
    public function index(Request $request): View
    {
        $tahun = (int) $request->get('tahun', date('Y'));
        if ($tahun < 2020 || $tahun > 2099) {
            $tahun = (int) date('Y');
        }

        return view('pimpinan.pnbp', [
            'tahun' => $tahun,
            'availableYears' => $this->service->getAvailableYears(),
        ]);
    }

    /**
     * Data PNBP (AJAX).
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
        ]);
    }
}
