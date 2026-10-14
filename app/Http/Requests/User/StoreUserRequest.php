<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'password' => [
                'required',
                'string',
                'confirmed',   // butuh password_confirmation
                Password::min(12)
                    ->letters()       // minimal 1 huruf
                    ->mixedCase()     // minimal 1 huruf besar & 1 huruf kecil
                    ->numbers()       // minimal 1 angka
                    ->symbols()       // minimal 1 simbol
                    ->uncompromised(), // cek di haveibeenpwned.com
            ],
            'role' => ['required', 'in:admin,keuangan,pimpinan,viewer'],
            'unit' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 12 karakter.',
            'password.letters' => 'Password harus mengandung minimal 1 huruf.',
            'password.mixed' => 'Password harus mengandung huruf besar & huruf kecil.',
            'password.numbers' => 'Password harus mengandung minimal 1 angka.',
            'password.symbols' => 'Password harus mengandung minimal 1 simbol (!@#$%^&*).',
            'password.uncompromised' => 'Password ini pernah bocor di internet. Gunakan password lain.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, dash, dan underscore.',
            'username.unique' => 'Username sudah dipakai.',
        ];
    }
}