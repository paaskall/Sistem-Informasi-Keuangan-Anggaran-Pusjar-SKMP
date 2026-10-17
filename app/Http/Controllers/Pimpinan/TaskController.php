<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $bulan = (int) $request->get('bulan', date('n'));
        $tahun = (int) $request->get('tahun', date('Y'));

        if ($bulan < 1 || $bulan > 12) $bulan = (int) date('n');
        if ($tahun < 2020 || $tahun > 2099) $tahun = (int) date('Y');

        return view('pimpinan.tasks', [
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $bulan = (int) $request->get('bulan', date('n'));
        $tahun = (int) $request->get('tahun', date('Y'));

        $tasks = Task::byMonth($bulan, $tahun)
            ->with(['assignedUser:id,name', 'creator:id,name'])
            ->orderByRaw("FIELD(status, 'proses', 'belum', 'selesai', 'dibatalkan')")
            ->orderBy('tanggal_mulai')
            ->get()
            ->map(fn ($t) => $this->transformTask($t));

        return response()->json([
            'ok' => true,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tasks' => $tasks,
            'stats' => Task::getStatsByMonth($bulan, $tahun),
        ]);
    }

    public function calendar(Request $request): JsonResponse
    {
        $bulan = (int) $request->get('bulan', date('n'));
        $tahun = (int) $request->get('tahun', date('Y'));

        return response()->json([
            'ok' => true,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'by_day' => Task::getCalendarData($bulan, $tahun),
            'stats' => Task::getStatsByMonth($bulan, $tahun),
        ]);
    }

    public function upcoming(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 5);
        if ($limit < 1 || $limit > 20) $limit = 5;

        $tasks = Task::upcoming($limit)
            ->with(['assignedUser:id,name'])
            ->get()
            ->map(fn ($t) => $this->transformTask($t));

        return response()->json([
            'ok' => true,
            'tasks' => $tasks,
        ]);
    }

    protected function transformTask(Task $t): array
    {
        return [
            'id' => $t->id,
            'judul' => $t->judul,
            'deskripsi' => $t->deskripsi,
            'bulan' => $t->bulan,
            'tahun' => $t->tahun,
            'tanggal_mulai' => $t->tanggal_mulai?->format('Y-m-d'),
            'tanggal_selesai' => $t->tanggal_selesai?->format('Y-m-d'),
            'tanggal_mulai_formatted' => $t->tanggal_mulai?->translatedFormat('d M Y'),
            'tanggal_selesai_formatted' => $t->tanggal_selesai?->translatedFormat('d M Y'),
            'status' => $t->status,
            'status_label' => $t->status_label,
            'status_color' => $t->status_color,
            'is_overdue' => $t->is_overdue,
            'hari_tersisa' => $t->hari_tersisa,
            'assigned_to' => $t->assigned_to,
            'assigned_name' => $t->assignedUser?->name,
            'creator_name' => $t->creator?->name,
            'lampiran' => $t->lampiran,
            'lampiran_url' => $t->lampiran ? asset('storage/' . $t->lampiran) : null,
            'created_at' => $t->created_at?->format('Y-m-d H:i'),
        ];
    }
}