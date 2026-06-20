<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sertifikasi_organik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petani_id')->constrained('petani')->cascadeOnDelete();
            $table->string('nomor_sertifikat')->unique();
            $table->string('lembaga_sertifikasi'); // mis. LSO yang menerbitkan
            $table->date('tanggal_terbit');
            $table->date('tanggal_kadaluarsa');
            $table->enum('status', ['aktif', 'kadaluarsa', 'dicabut'])->default('aktif');
            $table->string('file_dokumen')->nullable(); // path file scan sertifikat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sertifikasi_organik');
    }
};
