<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename jumlah_pohon → jumlah_kelapa
        Schema::table('lahans', function (Blueprint $table) {
            $table->renameColumn('jumlah_pohon', 'jumlah_kelapa');
        });

        // Tambah kolom baru
        Schema::table('lahans', function (Blueprint $table) {
            $table->string('blok_lahan')->nullable()->after('pemilik');
            $table->string('desa')->nullable()->after('blok_lahan');
            $table->decimal('luas_lahan', 8, 4)->nullable()->after('koordinat')->comment('dalam hektar');
            $table->integer('jumlah_nira')->default(0)->after('luas_lahan');
        });
    }

    public function down(): void
    {
        Schema::table('lahans', function (Blueprint $table) {
            $table->dropColumn(['blok_lahan', 'desa', 'luas_lahan', 'jumlah_nira']);
        });

        Schema::table('lahans', function (Blueprint $table) {
            $table->renameColumn('jumlah_kelapa', 'jumlah_pohon');
        });
    }
};
