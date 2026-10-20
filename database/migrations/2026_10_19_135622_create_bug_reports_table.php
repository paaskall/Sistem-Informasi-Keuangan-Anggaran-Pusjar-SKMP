<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bug_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul');
            $table->text('deskripsi');
            $table->enum('kategori', ['ui_ux', 'fungsional', 'performa', 'data', 'lainnya'])->default('fungsional');
            $table->enum('prioritas', ['rendah', 'sedang', 'tinggi', 'kritis'])->default('sedang');
            $table->enum('status', ['baru', 'diproses', 'selesai', 'ditolak'])->default('baru');
            $table->string('halaman_terkait')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bug_reports');
    }
};