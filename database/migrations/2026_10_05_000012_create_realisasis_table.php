<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_id')->constrained('targets')->cascadeOnDelete();
            $table->year('tahun');
            $table->tinyInteger('bulan');
            $table->date('tanggal');
            $table->decimal('nominal', 20, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tahun', 'bulan']);
            $table->index('target_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasis');
    }
};