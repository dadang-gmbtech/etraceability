<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lahans', function (Blueprint $table) {
            $table->string('pemilik')->nullable()->after('nama_lahan');
        });

        // Buat petani_id nullable: lahan bisa ada tanpa petani pengelola
        DB::statement('ALTER TABLE lahans ALTER COLUMN petani_id DROP NOT NULL');

        // Update FK constraint: SET NULL saat petani dihapus
        try {
            DB::statement('ALTER TABLE lahans DROP CONSTRAINT IF EXISTS lahans_petani_id_foreign');
            DB::statement('ALTER TABLE lahans ADD CONSTRAINT lahans_petani_id_foreign FOREIGN KEY (petani_id) REFERENCES petani(id) ON DELETE SET NULL');
        } catch (\Exception $e) {}
    }

    public function down(): void
    {
        Schema::table('lahans', function (Blueprint $table) {
            $table->dropColumn('pemilik');
        });
    }
};
