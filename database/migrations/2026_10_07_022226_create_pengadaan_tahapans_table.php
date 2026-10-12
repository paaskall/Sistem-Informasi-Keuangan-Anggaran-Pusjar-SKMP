<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengadaan_tahapans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengadaan_id')->constrained('pengadaans')->cascadeOnDelete();
            $table->string('nama', 255);
            $table->integer('urutan')->default(0);
            $table->boolean('selesai')->default(false);
            $table->date('tanggal_selesai')->nullable();
            $table->text('kendala')->nullable();
            $table->timestamps();

            $table->index(['pengadaan_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengadaan_tahapans');
    }
};