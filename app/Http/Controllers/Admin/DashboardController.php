<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\JenisPengadaan;
use App\Models\KategoriKendala;
use App\Models\KomponenBelanja;
use App\Models\Program;
use App\Models\Status;
use App\Models\SubKegiatan;
use App\Models\Tahapan;
use App\Models\Unit;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Halaman dashboard admin.
     */
    public function index(): View
    {
        $stats = [
            'total_user' => User::count(),
            'total_master' => collect([
                Program::count(),
                SubKegiatan::count(),
                KomponenBelanja::count(),
                Unit::count(),
                JenisPengadaan::count(),
                Tahapan::count(),
                Status::count(),
                KategoriKendala::count(),
            ])->sum(),
            'total_log' => AuditTrail::count(),
        ];

        $recentLogs = AuditTrail::with('user:id,name')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentLogs' => $recentLogs,
        ]);
    }
}