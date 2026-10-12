<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->tinyInteger('bulan');
            $table->string('unit', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tahun', 'bulan'], 'unique_target_per_month');
            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};