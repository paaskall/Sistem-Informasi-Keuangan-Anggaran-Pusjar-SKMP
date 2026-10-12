<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePengadaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isKeuangan();
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'kode' => [
                'required', 'string', 'max:30',
                Rule::unique('pengadaans', 'kode')->ignore($id)->whereNull('deleted_at'),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_pengadaan_id' => ['required', 'exists:jenis_pengadaans,id'],
            'nilai' => ['required', 'numeric', 'min:0'],
            'penyedia' => ['nullable', 'string', 'max:255'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'use_template' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required' => 'Kode pengadaan wajib diisi.',
            'kode.unique' => 'Kode pengadaan sudah dipakai.',
            'nama.required' => 'Nama pengadaan wajib diisi.',
            'jenis_pengadaan_id.required' => 'Jenis pengadaan wajib dipilih.',
            'nilai.required' => 'Nilai kontrak wajib diisi.',
            'nilai.min' => 'Nilai tidak boleh negatif.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah tanggal mulai.',
        ];
    }
}
