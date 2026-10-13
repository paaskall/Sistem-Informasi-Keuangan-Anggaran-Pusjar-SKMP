<?php

namespace App\Http\Controllers\Keuangan;

use App\Helpers\TahunHelper;
use App\Http\Controllers\Controller;
use App\Models\AkselerasiPenyerapan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AkselerasiController extends Controller
{
    public function index(Request $request): View
    {
        return view('keuangan.akselerasi');
    }

    public function data(Request $request): JsonResponse
    {
        $items = AkselerasiPenyerapan::query()
            ->terurut()
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'kegiatan' => $a->kegiatan,
                    'jangka_waktu' => $a->jangka_waktu,
                    'progress' => (int) $a->progress,
                    'keterangan' => $a->keterangan,
                    'urutan' => (int) $a->urutan,
                ];
            });

        return response()->json([
            'ok' => true,
            'items' => $items,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kegiatan' => 'required|string|max:255',
            'jangka_waktu' => 'required|string|max:100',
            'progress' => 'required|integer|min:0|max:100',
            'keterangan' => 'nullable|string',
            'urutan' => 'nullable|integer|min:0',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['urutan'] = $validated['urutan'] ?? (AkselerasiPenyerapan::max('urutan') + 1);

        $item = AkselerasiPenyerapan::create($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Data berhasil ditambahkan',
            'item' => $item,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $item = AkselerasiPenyerapan::findOrFail($id);

        $validated = $request->validate([
            'kegiatan' => 'required|string|max:255',
            'jangka_waktu' => 'required|string|max:100',
            'progress' => 'required|integer|min:0|max:100',
            'keterangan' => 'nullable|string',
            'urutan' => 'nullable|integer|min:0',
        ]);

        $item->update($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Data berhasil diupdate',
            'item' => $item,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $item = AkselerasiPenyerapan::findOrFail($id);
        $item->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Data berhasil dihapus',
        ]);
    }
}