<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTargetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isKeuangan();
    }

    public function rules(): array
    {
        $targetId = $this->route('id');

        return [
            'tahun' => ['required', 'integer', 'min:2020', 'max:2099'],
            'bulan' => [
                'required', 'integer', 'min:1', 'max:12',
                Rule::unique('targets', 'bulan')
                    ->where('tahun', $this->tahun)
                    ->whereNull('deleted_at')
                    ->ignore($targetId),
            ],
            'details' => ['required', 'array', 'min:1'],
            'details.*.program_id' => ['required', 'exists:programs,id'],
            'details.*.sub_kegiatan_id' => ['nullable', 'exists:sub_kegiatans,id'],
            'details.*.komponen_belanja_id' => ['required', 'exists:komponen_belanjas,id'],
            'details.*.nominal' => ['required', 'numeric', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'tahun.required' => 'Tahun wajib diisi.',
            'tahun.min' => 'Tahun minimal 2020.',
            'bulan.required' => 'Bulan wajib dipilih.',
            'bulan.unique' => 'Target untuk bulan ini sudah ada.',
            'details.required' => 'Minimal 1 komponen harus diisi.',
            'details.*.program_id.required' => 'Program wajib dipilih.',
            'details.*.komponen_belanja_id.required' => 'Komponen belanja wajib dipilih.',
            'details.*.nominal.required' => 'Nominal wajib diisi.',
            'details.*.nominal.min' => 'Nominal harus lebih dari 0.',
        ];
    }

    /**
     * Custom validation: cek duplikat kombinasi Program-Sub-Komponen.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $details = $this->input('details', []);
            $keys = [];
            foreach ($details as $i => $d) {
                $key = ($d['program_id'] ?? '-') . '-' . ($d['sub_kegiatan_id'] ?? '0') . '-' . ($d['komponen_belanja_id'] ?? '-');
                if (in_array($key, $keys)) {
                    $v->errors()->add("details.$i.komponen_belanja_id", 'Kombinasi Program-Sub-Komponen ini duplikat.');
                }
                $keys[] = $key;
            }
        });
    }
}