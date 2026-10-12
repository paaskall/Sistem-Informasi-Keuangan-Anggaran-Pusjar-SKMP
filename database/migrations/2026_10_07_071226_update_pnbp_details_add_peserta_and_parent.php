<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pnbp_details', function (Blueprint $table) {
            if (!Schema::hasColumn('pnbp_details', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('kategori_pnbp_id')
                    ->constrained('pnbp_details')->nullOnDelete();
            }
            if (!Schema::hasColumn('pnbp_details', 'peserta_target')) {
                $table->integer('peserta_target')->default(0)->after('realisasi');
            }
            if (!Schema::hasColumn('pnbp_details', 'peserta_realisasi')) {
                $table->integer('peserta_realisasi')->default(0)->after('peserta_target');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pnbp_details', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'peserta_target', 'peserta_realisasi']);
        });
    }
};