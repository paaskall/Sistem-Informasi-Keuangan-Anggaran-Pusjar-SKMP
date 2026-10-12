<?php

namespace App\Http\Controllers\Viewer;

use App\Http\Controllers\Controller;
use App\Services\PnbpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PnbpController extends Controller
{
    public function __construct(
        protected PnbpService $service
    ) {}

    public function index(Request $request): View
    {
        $tahun = (int) $request->get('tahun', date('Y'));
        if ($tahun < 2020 || $tahun > 2099) {
            $tahun = (int) date('Y');
        }

        return view('viewer.pnbp', [
            'tahun' => $tahun,
            'availableYears' => $this->service->getAvailableYears(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $tahun = (int) $request->get('tahun', date('Y'));

        return response()->json([
            'ok' => true,
            'tahun' => $tahun,
            'exists' => $this->service->getByYear($tahun) !== null,
            'pnbp' => $this->service->getByYear($tahun),
        ]);
    }
}
