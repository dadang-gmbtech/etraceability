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

    }

    public function down(): void
    {
        Schema::dropIfExists('pengepul');
    }
};
