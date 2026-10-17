<?php

namespace App\Http\Controllers\Keuangan;

use App\Helpers\TahunHelper;
use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service
    ) {}

    public function index(Request $request): View
    {
        $tahun = (int) $request->get('tahun', date('Y'));
        if ($tahun < 2020 || $tahun > 2099) {
            $tahun = (int) date('Y');
        }

        // Ambil flag SweetAlert, lalu hapus dari session
        $showReminder = session('show_login_reminder', false);
        session()->forget('show_login_reminder');

        return view('keuangan.dashboard', [
            'tahun' => $tahun,
            'tahunOptions' => [2026, 2025, 2024],
            'tahunBerjalan' => TahunHelper::tahunBerjalan(),
            'showReminder' => $showReminder,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $tahun = (int) $request->get('tahun', date('Y'));

        return response()->json([
            'ok' => true,
            'tahun' => $tahun,
            'editable' => TahunHelper::isEditable($tahun),
            'tahun_berjalan' => TahunHelper::tahunBerjalan(),
            'stats' => $this->service->getStats($tahun),
            'per_bulan' => $this->service->getPerBulan($tahun),
            'rencana_bulan_ini' => $this->service->getRencanaRealisasiBulanIni($tahun),
            'progress_pengadaan' => $this->service->getProgressPengadaan(3),
            'pnbp_summary' => $this->service->getPnbpSummary($tahun),
            'catatan_kendala' => $this->service->getCatatanKendala($tahun),
            'akselerasi' => $this->service->getAkselerasi($tahun),
            'kendala_pengadaan' => $this->service->getKendalaPengadaan($tahun),
            'rencana_pengadaan' => $this->service->getRencanaPengadaan($tahun),
            'upcoming_tasks' => $this->service->getUpcomingTasks(5),
        ]);
    }

    public function reminders(\App\Services\ReminderService $reminderService): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'reminders' => $reminderService->getReminders(),
        ]);
    }
}