<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setoran_gulas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_produksi_id')->constrained('batch_produksi')->onDelete('cascade');
            $table->foreignId('petani_id')->constrained('petani')->onDelete('cascade');
            $table->decimal('berat_kg', 8, 2);
            $table->date('tanggal_setor');
            $table->integer('hari_akumulasi')->default(1);
            $table->boolean('is_anomali')->default(false);
            $table->string('keterangan_anomali')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setoran_gulas');
    }
};
