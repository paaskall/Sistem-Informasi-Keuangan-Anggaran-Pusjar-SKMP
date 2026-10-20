<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DocumentationController extends Controller
{
    public function index(): View
    {
        return view('documentation', [
            'sections' => config('documentation.sections'),
        ]);
    }
}