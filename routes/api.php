<?php

use App\Http\Controllers\Api\IotController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes untuk perangkat IoT
|--------------------------------------------------------------------------
| Autentikasi menggunakan header: X-Device-Token: {api_token}
*/

Route::prefix('iot')->group(function () {
    // Kirim data sensor dari perangkat
    Route::post('/data', [IotController::class, 'store']);

    // Ping / cek status koneksi
    Route::get('/ping', [IotController::class, 'ping']);
});
