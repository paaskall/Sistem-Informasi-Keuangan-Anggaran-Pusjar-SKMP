<?php

namespace App\Http\Controllers\Viewer;

use App\Helpers\TahunHelper;
use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\TargetService;
use App\Services\RealisasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service,
        protected TargetService $targetService,
        protected RealisasiService $realisasiService
    ) {}

    public function index(Request $request): View
    {
        $tahun = (int) $request->get('tahun', date('Y'));
        if ($tahun < 2020 || $tahun > 2099) {
            $tahun = (int) date('Y');
        }

        return view('viewer.dashboard', [
            'tahun' => $tahun,
            'tahunOptions' => [2026, 2025, 2024],
            'tahunBerjalan' => TahunHelper::tahunBerjalan(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $tahun = (int) $request->get('tahun', date('Y'));

        return response()->json([
            'ok' => true,
            'tahun' => $tahun,
            'editable' => false,
            'tahun_berjalan' => \App\Helpers\TahunHelper::tahunBerjalan(),
            'stats' => $this->service->getStats($tahun),
            'per_bulan' => $this->service->getPerBulan($tahun),
            'pnbp_summary' => $this->service->getPnbpSummary($tahun),
            'progress_pengadaan' => $this->service->getProgressPengadaan(3),
            'rencana_bulan_ini' => $this->service->getRencanaRealisasiBulanIni($tahun),
            'bulan_ini' => $this->service->getBulanIni($tahun),
        ]);
    }
}
