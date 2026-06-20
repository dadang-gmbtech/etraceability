<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel log historis (audit trail). Setiap kali batch berpindah tangan
     * atau berubah status, satu baris dicatat di sini. Ini yang membuat
     * histori di portal publik bisa ditampilkan secara kronologis.
     */
    public function up(): void
    {
        Schema::create('event_traceability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_produksi_id')->constrained('batch_produksi')->cascadeOnDelete();
            $table->string('tipe_event'); // mis. "panen", "olah", "kumpul", "kirim", "terima"
            $table->string('aktor_tipe'); // mis. "petani", "pengrajin", "pengepul", "distributor"
            $table->unsignedBigInteger('aktor_id'); // id polymorphic ke tabel aktor terkait
            $table->string('lokasi_nama')->nullable();
            $table->decimal('lokasi_lat', 10, 7)->nullable();
            $table->decimal('lokasi_lng', 10, 7)->nullable();
            $table->text('catatan')->nullable();
            $table->dateTime('waktu_kejadian');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_traceability');
    }
};
