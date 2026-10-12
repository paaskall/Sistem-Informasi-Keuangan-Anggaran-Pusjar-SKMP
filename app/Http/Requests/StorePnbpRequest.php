<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePnbpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isKeuangan();
    }

    public function rules(): array
    {
        return [
            'tahun' => [
                'required', 'integer', 'min:2020', 'max:2099',
                Rule::unique('pnbps', 'tahun')->whereNull('deleted_at'),
            ],
            'unit' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'tahun.required' => 'Tahun wajib diisi.',
            'tahun.unique' => 'PNBP untuk tahun ini sudah ada.',
            'tahun.min' => 'Tahun minimal 2020.',
            'tahun.max' => 'Tahun maksimal 2099.',
        ];
    }
}
