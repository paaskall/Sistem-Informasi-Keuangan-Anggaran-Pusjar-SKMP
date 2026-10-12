<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pnbps', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->string('unit', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tahun'], 'unique_pnbp_per_year');
            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pnbps');
    }
};
