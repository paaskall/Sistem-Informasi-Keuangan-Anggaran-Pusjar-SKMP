<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akselerasi_penyerapans', function (Blueprint $table) {
            $table->id();
            $table->string('kegiatan');
            $table->string('jangka_waktu', 100);
            $table->tinyInteger('progress')->default(0);
            $table->text('keterangan')->nullable();
            $table->integer('urutan')->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('urutan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akselerasi_penyerapans');
    }
};