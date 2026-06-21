<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petani_id')->constrained('petani')->onDelete('cascade');
            $table->string('nama_lahan');
            $table->integer('jumlah_pohon')->default(0);
            // kolom geom (PostGIS) dihapus — koordinat disimpan sebagai JSON
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lahans');
    }
};
