<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Kendala;
use App\Models\KategoriKendala;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KendalaController extends Controller
{
    public function index(): View
    {
        return view('keuangan.kendala');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Kendala::with([
            'kategoriKendala:id,nama',
            'target:id,tahun,bulan',
            'realisasi:id,tanggal',
            'komponenBelanja:id,kode,nama',
        ]);

        if ($request->filled('kategori') && $request->kategori !== 'semua') {
            $query->where('kategori_kendala_id', $request->kategori);
        }

        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        $kendalas = $query->orderByDesc('tanggal')->get();

        $stats = [
            'total' => Kendala::count(),
            'proses' => Kendala::where('status', 'Proses')->count(),
            'selesai' => Kendala::where('status', 'Selesai')->count(),
        ];

        return response()->json([
            'ok' => true,
            'kendalas' => $kendalas,
            'stats' => $stats,
            'kategoris' => KategoriKendala::orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);

        $validated['user_id'] = auth()->id();
        $validated['periode'] = (int) date('n', strtotime($validated['tanggal']));

        $kendala = Kendala::create($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Kendala berhasil ditambahkan.',
            'kendala' => $kendala->load('kategoriKendala:id,nama'),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $kendala = Kendala::findOrFail($id);
        $validated = $this->validatePayload($request);

        $validated['periode'] = (int) date('n', strtotime($validated['tanggal']));

        $kendala->update($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Kendala berhasil diupdate.',
            'kendala' => $kendala->fresh()->load('kategoriKendala:id,nama'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $kendala = Kendala::findOrFail($id);
        $kendala->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Kendala berhasil dihapus.',
        ]);
    }

    protected function validatePayload(Request $request): array
    {
        return $request->validate([
            'tanggal' => ['required', 'date'],
            'kategori_kendala_id' => ['required', 'exists:kategori_kendalas,id'],
            'deskripsi' => ['required', 'string', 'max:500'],
            'dampak' => ['nullable', 'string', 'max:500'],
            'tindak_lanjut' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['Proses', 'Selesai'])],
        ], [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'kategori_kendala_id.required' => 'Kategori wajib dipilih.',
            'deskripsi.required' => 'Deskripsi wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
        ]);
    }
}