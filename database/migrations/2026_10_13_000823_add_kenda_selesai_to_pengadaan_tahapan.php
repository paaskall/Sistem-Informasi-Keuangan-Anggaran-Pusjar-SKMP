<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengadaan_tahapans', function (Blueprint $table) {
            $table->boolean('kendala_selesai')->default(false)->after('tindak_lanjut');
            $table->date('kendala_selesai_at')->nullable()->after('kendala_selesai');
        });
    }

    public function down(): void
    {
        Schema::table('pengadaan_tahapans', function (Blueprint $table) {
            $table->dropColumn(['kendala_selesai', 'kendala_selesai_at']);
        });
    }
};