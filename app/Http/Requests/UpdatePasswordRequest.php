<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'max:100',
                'different:current_password',
                'confirmed',   // butuh field new_password_confirmation
                Password::min(12)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'new_password_confirmation' => ['required', 'string', 'same:new_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 12 karakter.',
            'new_password.different' => 'Password baru harus berbeda dari password saat ini.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
            'new_password.letters' => 'Password harus mengandung minimal 1 huruf.',
            'new_password.mixed' => 'Password harus mengandung huruf besar & huruf kecil.',
            'new_password.numbers' => 'Password harus mengandung minimal 1 angka.',
            'new_password.symbols' => 'Password harus mengandung minimal 1 simbol (!@#$%^&*).',
            'new_password.uncompromised' => 'Password ini pernah bocor di internet. Gunakan password lain.',
            'new_password_confirmation.required' => 'Konfirmasi password wajib diisi.',
            'new_password_confirmation.same' => 'Konfirmasi password tidak cocok.',
        ];
    }

    /**
     * Custom validation: cek password lama benar.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $user = auth()->user();
            if (!$user || !Hash::check($this->current_password, $user->password)) {
                $v->errors()->add('current_password', 'Password saat ini salah.');
            }
        });
    }
}