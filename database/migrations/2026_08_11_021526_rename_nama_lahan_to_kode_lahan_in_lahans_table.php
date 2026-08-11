<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lahans', function (Blueprint $table) {
            $table->renameColumn('nama_lahan', 'kode_lahan');
        });

        Schema::table('lahans', function (Blueprint $table) {
            $table->string('kode_lahan', 9)->change();
        });
    }

    public function down(): void
    {
        Schema::table('lahans', function (Blueprint $table) {
            $table->string('kode_lahan')->change();
        });

        Schema::table('lahans', function (Blueprint $table) {
            $table->renameColumn('kode_lahan', 'nama_lahan');
        });
    }
};
