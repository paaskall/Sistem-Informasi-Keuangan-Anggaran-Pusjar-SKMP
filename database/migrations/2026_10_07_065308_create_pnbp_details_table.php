<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pnbp_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pnbp_id')->constrained('pnbps')->cascadeOnDelete();
            $table->foreignId('kategori_pnbp_id')->constrained('kategori_pnpbs')->restrictOnDelete();
            $table->tinyInteger('bulan');
            $table->decimal('target', 20, 2)->default(0);
            $table->decimal('realisasi', 20, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['pnbp_id', 'bulan']);
            $table->index('kategori_pnbp_id');
            $table->unique(['pnbp_id', 'kategori_pnbp_id', 'bulan'], 'unique_pnbp_detail');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pnbp_details');
    }
};
