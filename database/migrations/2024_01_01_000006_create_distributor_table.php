<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor', function (Blueprint $table) {
            $table->id();
            $table->string('kode_distributor')->unique();
            $table->string('nama_perusahaan');
            $table->string('no_hp')->nullable();
            $table->string('alamat')->nullable();
            $table->timestamps();
        });

        try {
            DB::statement('ALTER TABLE distributor ADD COLUMN lokasi_gudang geometry(Point, 4326)');
            DB::statement('CREATE INDEX distributor_lokasi_gist ON distributor USING GIST (lokasi_gudang)');
        } catch (\Exception $e) {
            // PostGIS tidak tersedia — kolom geometry dilewati
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor');
    }
};
