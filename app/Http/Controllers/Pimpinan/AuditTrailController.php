<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditTrailController extends Controller
{
    public function index(): View
    {
        return view('pimpinan.audit');
    }

    public function data(Request $request): JsonResponse
    {
        $query = AuditTrail::query()->with('user:id,name');

        if ($request->filled('user') && $request->user !== 'semua') {
            $query->where('user_name', $request->user);
        }

        if ($request->filled('module') && $request->module !== 'semua') {
            $query->where('module', $request->module);
        }

        if ($request->filled('action') && $request->action !== 'semua') {
            $query->where('action', $request->action);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        $perPage = (int) $request->get('per_page', 20);
        if ($perPage < 5) $perPage = 20;
        if ($perPage > 100) $perPage = 100;

        $logs = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'ok' => true,
            'logs' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'from' => $logs->firstItem(),
                'to' => $logs->lastItem(),
                'has_more' => $logs->hasMorePages(),
            ],
            'filters' => [
                'users' => AuditTrail::query()->distinct()->pluck('user_name')->filter()->values(),
                'modules' => AuditTrail::query()->distinct()->pluck('module')->filter()->values(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $log = AuditTrail::findOrFail($id);
        return response()->json(['ok' => true, 'log' => $log]);
    }
}