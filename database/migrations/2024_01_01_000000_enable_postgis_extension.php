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
        try {
            DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
        } catch (\Exception $e) {
            // PostGIS tidak terinstall di server ini — aplikasi menggunakan JSON untuk koordinat
        }
    }

    public function down(): void
    {
        try {
            DB::statement('DROP EXTENSION IF EXISTS postgis');
        } catch (\Exception $e) {
            //
        }
    }
};
