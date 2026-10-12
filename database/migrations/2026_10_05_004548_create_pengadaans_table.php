<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengadaans', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama', 255);
            $table->foreignId('jenis_pengadaan_id')->nullable()->constrained('jenis_pengadaans')->nullOnDelete();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();
            $table->tinyInteger('progress')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index('kode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengadaans');
    }
};