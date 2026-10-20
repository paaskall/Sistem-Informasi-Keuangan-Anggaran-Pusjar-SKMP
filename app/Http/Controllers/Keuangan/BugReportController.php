<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\BugReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BugReportController extends Controller
{
    public function index(): View
    {
        return view('keuangan.bug-report');
    }

    public function data(): JsonResponse
    {
        $reports = BugReport::with('user:id,name')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'judul' => $r->judul,
                'deskripsi' => $r->deskripsi,
                'kategori' => $r->kategori,
                'kategori_label' => $r->kategori_label,
                'prioritas' => $r->prioritas,
                'prioritas_label' => $r->prioritas_label,
                'prioritas_color' => $r->prioritas_color,
                'status' => $r->status,
                'status_label' => $r->status_label,
                'status_color' => $r->status_color,
                'halaman_terkait' => $r->halaman_terkait,
                'catatan_admin' => $r->catatan_admin,
                'created_at' => $r->created_at?->format('d M Y H:i'),
            ]);

        return response()->json([
            'ok' => true,
            'reports' => $reports,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:2000',
            'kategori' => 'required|in:ui_ux,fungsional,performa,data,lainnya',
            'prioritas' => 'required|in:rendah,sedang,tinggi,kritis',
            'halaman_terkait' => 'nullable|string|max:255',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'baru';

        $report = BugReport::create($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Laporan bug berhasil dikirim. Terima kasih!',
            'report' => $report,
        ]);
    }
}