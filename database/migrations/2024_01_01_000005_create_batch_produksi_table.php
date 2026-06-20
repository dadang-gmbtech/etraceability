<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_produksi', function (Blueprint $table) {
            $table->id();
            $table->string('trace_id')->unique(); // e.g. GKO-20240101-001
            
            // Pengepul / KUB
            $table->foreignId('pengepul_id')->nullable()->constrained('pengepul')->onDelete('set null');
            $table->date('tanggal_pengumpulan')->nullable();
            
            // Status batch
            $table->string('status_batch')->default('dikumpulkan');
            $table->boolean('is_organik')->default(false);
            
            // Total dari semua setoran
            $table->decimal('berat_total_kg', 10, 2)->default(0);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_produksi');
    }
};
