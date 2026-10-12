<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePnbpDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isKeuangan();
    }

    public function rules(): array
    {
        return [
            'target' => ['nullable', 'numeric', 'min:0'],
            'realisasi' => ['nullable', 'numeric', 'min:0'],
            'peserta_target' => ['nullable', 'integer', 'min:0'],
            'peserta_realisasi' => ['nullable', 'integer', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'target.min' => 'Target tidak boleh negatif.',
            'realisasi.min' => 'Realisasi tidak boleh negatif.',
        ];
    }
}
