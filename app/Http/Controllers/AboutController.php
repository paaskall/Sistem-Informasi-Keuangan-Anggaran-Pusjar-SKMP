<?php

namespace App\Http\Controllers;

use App\Models\Kendala;
use App\Models\Realisasi;
use App\Models\Target;
use App\Models\User;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::where('active', true)->count(),
            'total_target' => Target::count(),
            'total_realisasi' => Realisasi::count(),
            'total_kendala_aktif' => Kendala::where('status', 'Proses')->count(),
        ];

        return view('about', compact('stats'));
    }
}