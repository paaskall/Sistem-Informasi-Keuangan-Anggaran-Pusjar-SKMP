<?php

namespace App\Http\Controllers\User;

use App\Helpers\TahunHelper;
use App\Http\Controllers\Controller;
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

    public function index(Request $request): View
    {
        $tahun = (int) $request->get('tahun', date('Y'));
        if ($tahun < 2020 || $tahun > 2099) {
            $tahun = (int) date('Y');
        }

        return view('user.realisasi', [
            'tahun' => $tahun,
            'tahunOptions' => [2026, 2025, 2024],
            'tahunBerjalan' => TahunHelper::tahunBerjalan(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $tahun = (int) $request->get('tahun', date('Y'));

        $targets = Target::where('tahun', $tahun)->get()->keyBy('id');
        $realisasis = $this->service->getRealisasisByYear($tahun);

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
            'realisasis' => $realisasis,
            'total_target' => $this->targetService->getTotalTahunan($tahun),
            'total_realisasi' => $this->service->getTotalTahunan($tahun),
            'editable' => false,
        ]);
    }
}
