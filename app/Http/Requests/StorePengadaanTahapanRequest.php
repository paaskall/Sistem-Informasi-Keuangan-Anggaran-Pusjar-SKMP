<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengadaanTahapanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isKeuangan();
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'selesai' => ['nullable', 'boolean'],
            'tanggal_selesai' => ['nullable', 'date'],
            'kendala' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama tahapan wajib diisi.',
            'nama.max' => 'Nama tahapan maksimal 255 karakter.',
        ];
    }
}
