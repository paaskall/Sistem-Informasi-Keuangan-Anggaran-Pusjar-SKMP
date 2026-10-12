<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreMasterRequest;
use App\Http\Requests\Master\StoreProgramRequest;
use App\Http\Requests\Master\StoreSubKegiatanRequest;
use App\Services\MasterDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterController extends Controller
{
    public function __construct(
        protected MasterDataService $service
    ) {}

    /**
     * Halaman utama master data (dengan tab).
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'program');
        $tabs = $this->service->allTabs();

        if (!isset($tabs[$tab])) {
            $tab = 'program';
        }

        $items = $this->service->getItems($tab);
        $references = $this->service->getReferenceData();

        return view('admin.master', [
            'activeTab' => $tab,
            'tabConfig' => $tabs,
            'items' => $items,
            'references' => $references,
        ]);
    }

    /**
     * Ambil items untuk tab tertentu (via AJAX).
     */
    public function items(string $tab): JsonResponse
    {
        $items = $this->service->getItems($tab);
        $references = $this->service->getReferenceData();

        return response()->json([
            'ok' => true,
            'tab' => $tab,
            'items' => $items,
            'references' => $references,
        ]);
    }

    /**
     * Simpan item baru.
     */
    public function store(Request $request, string $tab): JsonResponse
    {
        $tabConfig = $this->service->getTab($tab);
        $modelClass = $tabConfig['model'];

        $validated = $this->validatePayload($request, $tab);

        $item = $modelClass::create($validated);

        return response()->json([
            'ok' => true,
            'message' => "{$tabConfig['label']} berhasil ditambahkan.",
            'item' => $item,
        ]);
    }

    /**
     * Update item.
     */
    public function update(Request $request, string $tab, int $id): JsonResponse
    {
        $tabConfig = $this->service->getTab($tab);
        $modelClass = $tabConfig['model'];

        $item = $modelClass::findOrFail($id);
        $validated = $this->validatePayload($request, $tab, $id);

        $item->update($validated);

        return response()->json([
            'ok' => true,
            'message' => "{$tabConfig['label']} berhasil diupdate.",
            'item' => $item->fresh(),
        ]);
    }

    /**
     * Hapus item (soft delete).
     */
    public function destroy(string $tab, int $id): JsonResponse
    {
        $tabConfig = $this->service->getTab($tab);
        $modelClass = $tabConfig['model'];

        $item = $modelClass::findOrFail($id);

        // Cek referensi
        $warnings = $this->service->getDeleteWarnings($tab, $id);

        $item->delete();

        return response()->json([
            'ok' => true,
            'message' => "{$tabConfig['label']} berhasil dihapus.",
            'warnings' => $warnings,
        ]);
    }

    /**
     * Validasi dinamis sesuai tab.
     */
    protected function validatePayload(Request $request, string $tab, ?int $id = null): array
    {
        if ($tab === 'program') {
            return $request->validate([
                'kode' => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::unique('programs', 'kode')->ignore($id)],
                'nama' => ['required', 'string', 'max:255'],
            ]);
        }

        if ($tab === 'subKegiatan') {
            return $request->validate([
                'program_id' => ['required', 'exists:programs,id'],
                'kode' => ['required', 'string', 'max:50'],
                'nama' => ['required', 'string', 'max:255'],
            ]);
        }

        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
        ]);
    }
}