<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah enum role untuk tambah 'viewer'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'keuangan', 'pimpinan', 'viewer') NOT NULL DEFAULT 'viewer'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'keuangan', 'pimpinan') NOT NULL DEFAULT 'keuangan'");
    }
};