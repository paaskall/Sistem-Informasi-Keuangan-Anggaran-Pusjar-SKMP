<?php

namespace App\Http\Controllers\User;

use App\Helpers\TahunHelper;
use App\Http\Controllers\Controller;
use App\Services\TargetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TargetController extends Controller
{
    public function __construct(
        protected TargetService $service
    ) {}

    public function index(Request $request): View
    {
        $tahun = (int) $request->get('tahun', date('Y'));
        if ($tahun < 2020 || $tahun > 2099) {
            $tahun = (int) date('Y');
        }

        return view('user.target', [
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
            'targets' => $this->service->getTargetsByYear($tahun),
            'total_tahunan' => $this->service->getTotalTahunan($tahun),
            'references' => $this->service->getReferenceData(),
            'editable' => false,
        ]);
    }
}
