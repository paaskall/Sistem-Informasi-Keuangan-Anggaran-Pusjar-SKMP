<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kendalas', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->tinyInteger('periode');
            $table->foreignId('kategori_kendala_id')->nullable()->constrained('kategori_kendalas')->nullOnDelete();
            $table->text('deskripsi');
            $table->text('dampak')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->enum('status', ['Proses', 'Selesai'])->default('Proses');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['periode', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kendalas');
    }
};