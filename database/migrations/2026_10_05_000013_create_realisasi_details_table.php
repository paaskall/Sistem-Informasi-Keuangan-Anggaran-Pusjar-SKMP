<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisasi_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisasi_id')->constrained('realisasis')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->restrictOnDelete();
            $table->foreignId('sub_kegiatan_id')->nullable()->constrained('sub_kegiatans')->nullOnDelete();
            $table->foreignId('komponen_belanja_id')->constrained('komponen_belanjas')->restrictOnDelete();
            $table->decimal('nominal', 20, 2)->default(0);
            $table->timestamps();

            $table->index(['realisasi_id', 'program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasi_details');
    }
};