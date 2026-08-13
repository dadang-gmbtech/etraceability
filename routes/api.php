<?php

use App\Http\Controllers\Api\IotController;
use App\Http\Controllers\Api\PetaniApiController;
use App\Http\Middleware\PetaniApiAuth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes untuk perangkat IoT
|--------------------------------------------------------------------------
| Autentikasi menggunakan header: X-Device-Token: {api_token}
*/

// --- API untuk Aplikasi Android Petani ---
Route::prefix('v1/petani')->name('api.petani.')->group(function () {
    // Login — tidak butuh token
    Route::post('login', [PetaniApiController::class, 'login'])->name('login');

    // Endpoint yang butuh token
    Route::middleware(PetaniApiAuth::class)->group(function () {
        Route::get('profil',  [PetaniApiController::class, 'profil'])->name('profil');
        Route::get('lahan',   [PetaniApiController::class, 'lahan'])->name('lahan');
        Route::get('setoran', [PetaniApiController::class, 'setoran'])->name('setoran');
        Route::get('rekap',   [PetaniApiController::class, 'rekap'])->name('rekap');
    });
});

// --- API IoT ---
Route::prefix('iot')->group(function () {
    // Kirim data sensor dari perangkat
    Route::post('/data', [IotController::class, 'store']);

    // Ping / cek status koneksi
    Route::get('/ping', [IotController::class, 'ping']);
});
