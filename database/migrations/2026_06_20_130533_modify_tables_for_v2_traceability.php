<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('petani', function (Blueprint $table) {
                if (Schema::hasColumn('petani', 'lokasi_kebun_lat')) {
                    $table->dropColumn('lokasi_kebun_lat');
                }
                if (Schema::hasColumn('petani', 'lokasi_kebun_lng')) {
                    $table->dropColumn('lokasi_kebun_lng');
                }
            });
        } catch (\Exception $e) {}
        
        // Remove PostGIS geometry if exists
        try {
            DB::statement('DROP INDEX IF EXISTS petani_lokasi_kebun_gist');
            DB::statement('ALTER TABLE petani DROP COLUMN IF EXISTS lokasi_kebun');
        } catch (\Exception $e) {
            // ignore if not exists
        }

        // 2. Modifikasi tabel Lahans
        Schema::table('lahans', function (Blueprint $table) {
            $table->string('jenis_geometri')->default('polygon'); // 'titik' atau 'polygon'
            $table->json('koordinat')->nullable();
        });
        
        try {
            DB::statement('ALTER TABLE lahans DROP COLUMN IF EXISTS geom');
        } catch (\Exception $e) {
            // ignore
        }

        // 3. Tabel Harga Harians
        Schema::create('harga_harians', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('jenis_produk');
            $table->decimal('harga_per_kg', 10, 2);
            $table->timestamps();
        });

        // 4. Modifikasi tabel Setoran Gula
        Schema::table('setoran_gulas', function (Blueprint $table) {
            $table->foreignId('pengepul_id')->nullable()->constrained('pengepul')->onDelete('set null');
            $table->string('jenis_produk')->default('gula semut');
            $table->decimal('total_harga', 12, 2)->default(0);
        });
        
        // Make batch_produksi_id nullable
        DB::statement('ALTER TABLE setoran_gulas ALTER COLUMN batch_produksi_id DROP NOT NULL');

        // 5. Integrasi GIS-IoT: Devices table
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lahan_id')->nullable()->constrained('lahans')->onDelete('set null');
            $table->string('name');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 6. Integrasi GIS-IoT: Soil Measurements
        Schema::create('soil_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->decimal('ph_level', 5, 2)->nullable();
            $table->decimal('moisture', 5, 2)->nullable();
            $table->decimal('nitrogen', 5, 2)->nullable();
            $table->decimal('phosphorus', 5, 2)->nullable();
            $table->decimal('potassium', 5, 2)->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soil_measurements');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('harga_harians');
        
        Schema::table('setoran_gulas', function (Blueprint $table) {
            $table->dropForeign(['pengepul_id']);
            $table->dropColumn(['pengepul_id', 'jenis_produk', 'total_harga']);
        });

        Schema::table('lahans', function (Blueprint $table) {
            $table->dropColumn(['jenis_geometri', 'koordinat']);
        });
    }
};
