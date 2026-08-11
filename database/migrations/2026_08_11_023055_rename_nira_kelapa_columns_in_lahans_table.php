<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lahans', function (Blueprint $table) {
            $table->renameColumn('jumlah_nira', 'pohon_di_deres');
            $table->renameColumn('jumlah_kelapa', 'kelapa_buah');
        });
    }

    public function down(): void
    {
        Schema::table('lahans', function (Blueprint $table) {
            $table->renameColumn('pohon_di_deres', 'jumlah_nira');
            $table->renameColumn('kelapa_buah', 'jumlah_kelapa');
        });
    }
};
