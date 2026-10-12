<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class MasterDataService
{
    /**
     * Ambil konfigurasi tab berdasarkan key.
     */
    public function getTab(string $key): array
    {
        $tabs = config('master.tabs', []);
        if (!isset($tabs[$key])) {
            abort(404, "Master tab '{$key}' tidak ditemukan.");
        }
        return $tabs[$key];
    }

    /**
     * Ambil semua konfigurasi tab.
     */
    public function allTabs(): array
    {
        return config('master.tabs', []);
    }

    /**
     * Ambil semua item dari tab tertentu.
     */
    public function getItems(string $tabKey): array
    {
        $tab = $this->getTab($tabKey);
        $model = new $tab['model'];
        return $model->orderBy('id')->get()->toArray();
    }

    /**
     * Ambil semua data master untuk dropdown referensi.
     */
    public function getReferenceData(): array
    {
        $ref = [];
        foreach (config('master.tabs', []) as $key => $tab) {
            $model = new $tab['model'];
            $ref[$key] = $model->orderBy('id')->get(['id', 'nama'])->toArray();

            // Sub Kegiatan butuh tambahan kode & program_id
            if ($key === 'subKegiatan') {
                $ref[$key] = $model->orderBy('id')->get(['id', 'nama', 'kode', 'program_id'])->toArray();
            }
            // Program & Komponen butuh kode
            if (in_array($key, ['program', 'komponenBelanja'])) {
                $ref[$key] = $model->orderBy('id')->get(['id', 'nama', 'kode'])->toArray();
            }
        }
        return $ref;
    }

    /**
     * Cek apakah item masih dipakai oleh record lain.
     * Return array of warning strings (kosong kalau aman).
     */
    public function getDeleteWarnings(string $tabKey, int $id): array
    {
        $warnings = [];

        if ($tabKey === 'program') {
            $count = \App\Models\SubKegiatan::where('program_id', $id)->count();
            if ($count > 0) {
                $warnings[] = "{$count} sub-kegiatan terkait akan ikut terhapus.";
            }
            $countTarget = \App\Models\TargetDetail::where('program_id', $id)->count();
            if ($countTarget > 0) {
                $warnings[] = "Program ini dipakai di {$countTarget} detail target.";
            }
        }

        if ($tabKey === 'subKegiatan') {
            $count = \App\Models\TargetDetail::where('sub_kegiatan_id', $id)->count();
            if ($count > 0) {
                $warnings[] = "Dipakai di {$count} detail target.";
            }
        }

        if ($tabKey === 'komponenBelanja') {
            $count = \App\Models\TargetDetail::where('komponen_belanja_id', $id)->count();
            if ($count > 0) {
                $warnings[] = "Dipakai di {$count} detail target.";
            }
        }

        if ($tabKey === 'jenisPengadaan') {
            $count = \App\Models\Pengadaan::where('jenis_pengadaan_id', $id)->count();
            if ($count > 0) {
                $warnings[] = "Dipakai di {$count} pengadaan.";
            }
        }

        if ($tabKey === 'status') {
            $count = \App\Models\Pengadaan::where('status_id', $id)->count();
            if ($count > 0) {
                $warnings[] = "Dipakai di {$count} pengadaan.";
            }
        }

        if ($tabKey === 'kategoriKendala') {
            $count = \App\Models\Kendala::where('kategori_kendala_id', $id)->count();
            if ($count > 0) {
                $warnings[] = "Dipakai di {$count} kendala.";
            }
        }

        return $warnings;
    }
}