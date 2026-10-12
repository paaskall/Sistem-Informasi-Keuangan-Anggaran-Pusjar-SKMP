<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->string('kode', 50);
            $table->string('nama', 255);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['program_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_kegiatans');
    }
};