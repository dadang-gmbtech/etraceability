<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mengaktifkan ekstensi PostGIS di database PostgreSQL.
     * WAJIB dijalankan paling pertama sebelum migration lain yang
     * menggunakan tipe kolom geometry/geography.
     */
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
    }

    public function down(): void
    {
        DB::statement('DROP EXTENSION IF EXISTS postgis');
    }
};
