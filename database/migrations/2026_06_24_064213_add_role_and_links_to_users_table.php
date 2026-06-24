<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->after('email');
            $table->foreignId('petani_id')->nullable()->constrained('petani')->nullOnDelete()->after('role');
            $table->foreignId('pengepul_id')->nullable()->constrained('pengepul')->nullOnDelete()->after('petani_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('petani_id');
            $table->dropConstrainedForeignId('pengepul_id');
            $table->dropColumn('role');
        });
    }
};
