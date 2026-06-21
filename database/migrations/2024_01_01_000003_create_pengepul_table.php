<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengepul', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pengepul')->unique(); // mis. PPL-0001
            $table->string('nama_koperasi');
            $table->string('penanggung_jawab')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('alamat')->nullable();
            $table->timestamps();
        });

        try {
            DB::statement('ALTER TABLE pengepul ADD COLUMN lokasi_gudang geometry(Point, 4326)');
            DB::statement('CREATE INDEX pengepul_lokasi_gist ON pengepul USING GIST (lokasi_gudang)');
        } catch (\Exception $e) {
            // PostGIS tidak tersedia — kolom geometry dilewati
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengepul');
    }
};
