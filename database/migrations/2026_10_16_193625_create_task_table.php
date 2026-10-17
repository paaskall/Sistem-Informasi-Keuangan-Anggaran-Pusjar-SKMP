<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->string('judul');
            $table->text('deskripsi')->nullable();

            // Kategori periode (bulan & tahun)
            $table->tinyInteger('bulan')->unsigned();
            $table->smallInteger('tahun')->unsigned();

            // Tanggal pelaksanaan
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();

            // Status
            $table->enum('status', ['belum', 'proses', 'selesai', 'dibatalkan'])
                ->default('belum');

            // Assignee (siapa PIC) & Creator (siapa yang buat)
            $table->foreignId('assigned_to')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');

            // Lampiran (opsional)
            $table->string('lampiran')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes untuk performa
            $table->index(['tahun', 'bulan']);
            $table->index('status');
            $table->index('assigned_to');
            $table->index('tanggal_mulai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};