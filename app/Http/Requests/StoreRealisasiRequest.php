<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRealisasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isKeuangan();
    }

    public function rules(): array
    {
        return [
            'target_id' => ['required', 'exists:targets,id'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.program_id' => ['required', 'exists:programs,id'],
            'details.*.sub_kegiatan_id' => ['nullable', 'exists:sub_kegiatans,id'],
            'details.*.komponen_belanja_id' => ['required', 'exists:komponen_belanjas,id'],
            'details.*.nominal' => ['required', 'numeric', 'min:0'],
            // Kendala (opsional)
            'kendalas' => ['nullable', 'array'],
            'kendalas.*.kategori_kendala_id' => ['required', 'exists:kategori_kendalas,id'],
            'kendalas.*.komponen_belanja_id' => ['nullable', 'exists:komponen_belanjas,id'],
            'kendalas.*.deskripsi' => ['required', 'string', 'max:500'],
            'kendalas.*.dampak' => ['nullable', 'string', 'max:500'],
            'kendalas.*.tindak_lanjut' => ['nullable', 'string', 'max:500'],
            'kendalas.*.status' => ['required', 'in:Proses,Selesai'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_id.required' => 'Target wajib dipilih.',
            'tanggal.required' => 'Tanggal realisasi wajib diisi.',
            'details.required' => 'Minimal 1 komponen harus diisi.',
            'details.*.nominal.required' => 'Nominal wajib diisi.',
            'details.*.nominal.min' => 'Nominal minimal 0.',
        ];
    }
}