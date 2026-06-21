<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petani', function (Blueprint $table) {
            $table->id();
            $table->string('kode_petani')->unique(); // mis. PTN-0001
            $table->string('nama');
            $table->string('no_hp')->nullable();
            $table->string('alamat')->nullable();
            $table->string('desa')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // Kolom geometry untuk lokasi kebun (point) ditambah lewat raw SQL
        // karena PostGIS belum punya native column type di Laravel schema builder.
    }

    public function down(): void
    {
        Schema::dropIfExists('petani');
    }
};
