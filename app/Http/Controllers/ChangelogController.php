<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ChangelogController extends Controller
{
    public function index(): View
    {
        return view('changelog', [
            'releases' => config('changelog.releases'),
        ]);
    }
}