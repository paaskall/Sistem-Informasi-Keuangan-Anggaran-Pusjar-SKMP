<?php

namespace App\Http\Controllers\Viewer;

use App\Http\Controllers\Controller;
use App\Models\JenisPengadaan;
use App\Models\Pengadaan;
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
        return view('viewer.pengadaan');
    }

    public function detail(int $id): View
    {
        $pengadaan = Pengadaan::findOrFail($id);
        return view('viewer.pengadaan-detail', ['pengadaanId' => $pengadaan->id]);
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
            'jenis_list' => JenisPengadaan::orderBy('nama')->get(['id', 'nama']),
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
}
