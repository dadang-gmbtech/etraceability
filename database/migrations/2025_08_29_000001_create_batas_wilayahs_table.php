<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('batas_wilayahs', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 20); // 'kecamatan' | 'desa'
            $table->string('nama');
            $table->string('kode', 100)->nullable();
            $table->json('koordinat');
            $table->timestamps();
            $table->index('jenis');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batas_wilayahs');
    }
};
