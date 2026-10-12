<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'kode' => [
                'required', 'string', 'max:50',
                Rule::unique('programs', 'kode')->ignore($id),
            ],
            'nama' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required' => 'Kode program wajib diisi.',
            'kode.unique' => 'Kode program sudah dipakai.',
            'nama.required' => 'Nama program wajib diisi.',
        ];
    }
}