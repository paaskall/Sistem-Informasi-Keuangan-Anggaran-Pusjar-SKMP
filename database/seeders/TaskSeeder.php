<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin_keuangan')->first();
        $keuangan = User::where('role', 'keuangan')->first();

        if (!$admin || !$keuangan) {
            $this->command->warn('User admin_keuangan atau keuangan tidak ditemukan. Skip TaskSeeder.');
            return;
        }

        $today = Carbon::today();
        $bulan = $today->month;
        $tahun = $today->year;

        // Hapus task lama (opsional, biar tidak duplikat)
        Task::where('bulan', $bulan)->where('tahun', $tahun)->forceDelete();

        $tasks = [
            // Task overdue (tanggal selesai sudah lewat, belum selesai)
            [
                'judul' => 'Kirim Laporan Final ke Pimpinan',
                'deskripsi' => 'Laporan final target vs realisasi sudah harus dikirim.',
                'tanggal_mulai' => $today->copy()->subDays(5),
                'tanggal_selesai' => $today->copy()->subDays(2),
                'status' => 'belum',
                'assigned_to' => $admin->id,
            ],
            // Task hari ini
            [
                'judul' => 'Input Realisasi Hari Ini',
                'deskripsi' => 'Input data realisasi anggaran untuk komponen belanja hari ini.',
                'tanggal_mulai' => $today->copy(),
                'tanggal_selesai' => $today->copy(),
                'status' => 'proses',
                'assigned_to' => $keuangan->id,
            ],
            // Task besok
            [
                'judul' => 'Review Laporan Bulanan',
                'deskripsi' => 'Review laporan target vs realisasi sebelum dikirim.',
                'tanggal_mulai' => $today->copy()->addDays(1),
                'tanggal_selesai' => $today->copy()->addDays(1),
                'status' => 'belum',
                'assigned_to' => $keuangan->id,
            ],
            // Task 3 hari lagi
            [
                'judul' => 'Rapat Evaluasi Triwulan',
                'deskripsi' => 'Rapat evaluasi capaian anggaran triwulan dengan pimpinan.',
                'tanggal_mulai' => $today->copy()->addDays(3),
                'tanggal_selesai' => $today->copy()->addDays(3),
                'status' => 'belum',
                'assigned_to' => $admin->id,
            ],
            // Task 7 hari lagi
            [
                'judul' => 'Update Data PNBP',
                'deskripsi' => 'Update data penerimaan PNBP bulan ini.',
                'tanggal_mulai' => $today->copy()->addDays(7),
                'tanggal_selesai' => $today->copy()->addDays(7),
                'status' => 'belum',
                'assigned_to' => $keuangan->id,
            ],
            // Task selesai (untuk demo status)
            [
                'judul' => 'Input Target Awal Bulan',
                'deskripsi' => 'Input target awal bulan — sudah selesai.',
                'tanggal_mulai' => $today->copy()->subDays(10),
                'tanggal_selesai' => $today->copy()->subDays(10),
                'status' => 'selesai',
                'assigned_to' => $keuangan->id,
            ],
        ];

        foreach ($tasks as $task) {
            Task::create(array_merge($task, [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'created_by' => $admin->id,
            ]));
        }

        $this->command->info('TaskSeeder: ' . count($tasks) . ' task berhasil dibuat untuk ' . $bulan . '/' . $tahun);
    }
}