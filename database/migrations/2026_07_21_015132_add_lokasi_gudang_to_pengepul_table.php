<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pengepul ADD COLUMN lokasi_gudang geometry(Point, 4326) NULL");
        DB::statement("CREATE INDEX pengepul_lokasi_gudang_gist ON pengepul USING GIST (lokasi_gudang)");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS pengepul_lokasi_gudang_gist");
        DB::statement("ALTER TABLE pengepul DROP COLUMN IF EXISTS lokasi_gudang");
    }
};
