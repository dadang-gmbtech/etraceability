<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rute_distribusi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_produksi_id')->constrained('batch_produksi')->cascadeOnDelete();
            $table->foreignId('distributor_id')->nullable()->constrained('distributor');
            $table->string('asal'); // nama lokasi asal, mis. "Gudang Pengepul Kulon Progo"
            $table->string('tujuan'); // nama lokasi tujuan
            $table->dateTime('waktu_berangkat')->nullable();
            $table->dateTime('waktu_tiba')->nullable();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('rute_distribusi');
    }
};
