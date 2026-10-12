<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengadaans', function (Blueprint $table) {
            if (!Schema::hasColumn('pengadaans', 'nilai')) {
                $table->decimal('nilai', 20, 2)->default(0)->after('nama');
            }
            if (!Schema::hasColumn('pengadaans', 'penyedia')) {
                $table->string('penyedia', 255)->nullable()->after('nilai');
            }
            if (!Schema::hasColumn('pengadaans', 'tanggal_mulai')) {
                $table->date('tanggal_mulai')->nullable()->after('penyedia');
            }
            if (!Schema::hasColumn('pengadaans', 'tanggal_selesai')) {
                $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengadaans', function (Blueprint $table) {
            $table->dropColumn(['nilai', 'penyedia', 'tanggal_mulai', 'tanggal_selesai']);
        });
    }
};