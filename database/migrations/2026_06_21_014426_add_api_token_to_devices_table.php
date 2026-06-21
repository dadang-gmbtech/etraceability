<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('api_token', 64)->unique()->nullable()->after('status');
        });

        // Generate token untuk perangkat yang sudah ada
        DB::table('devices')->whereNull('api_token')->orderBy('id')->each(function ($device) {
            DB::table('devices')
                ->where('id', $device->id)
                ->update(['api_token' => Str::random(48)]);
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('api_token');
        });
    }
};
