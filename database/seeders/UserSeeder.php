<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'username' => 'admin',
                'password' => Hash::make('admin'),
                'name' => 'Siti',
                'role' => 'admin',
                'unit' => 'Bagian Umum',
                'active' => true,
            ],
            [
                'username' => 'keuangan',
                'password' => Hash::make('keuangan'),
                'name' => 'Ahmad',
                'role' => 'keuangan',
                'unit' => 'Bagian Keuangan',
                'active' => true,
            ],
            [
                'username' => 'budi',
                'password' => Hash::make('budi'),
                'name' => 'Budi',
                'role' => 'keuangan',
                'unit' => 'Bagian Keuangan',
                'active' => true,
            ],
            [
                'username' => 'pimpinan',
                'password' => Hash::make('pimpinan'),
                'name' => 'Pimpinan',
                'role' => 'pimpinan',
                'unit' => '-',
                'active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['username' => $user['username']],
                $user
            );
        }
    }
}