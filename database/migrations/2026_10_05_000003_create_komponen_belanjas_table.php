<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komponen_belanjas', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('nama', 255);
            $table->timestamps();
            $table->softDeletes();
            $table->index('kode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komponen_belanjas');
    }
};