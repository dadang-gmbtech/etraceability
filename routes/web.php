<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\PetaniManager;
use App\Livewire\BatchProduksiManager;
use App\Livewire\BatchTraceabilityView;
use App\Livewire\TracePublic;
use App\Livewire\PetaSebaran;
use App\Livewire\RuteDistribusiManager;
use App\Livewire\PetaniDashboard;
use App\Livewire\PengepulDashboard;
use App\Livewire\KubDashboard;
use App\Http\Controllers\Auth\GoogleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Halaman publik: konsumen scan QR code di kemasan akan diarahkan ke sini ---
Route::get('/lacak/{trace_id}', TracePublic::class)->name('trace.public');

// --- Traceability publik per batch ---
Route::get('/batch/{trace_id}/traceability', BatchTraceabilityView::class)->name('batch.traceability');

// --- Cetak QR Code Batch (internal/admin) ---
Route::get('/batch-produksi/{trace_id}/cetak-qr', function ($trace_id) {
    $batch = \App\Models\BatchProduksi::where('trace_id', $trace_id)->firstOrFail();
    return view('cetak-qr', compact('batch'));
})->name('batch.qrcode');

// --- Cetak QR Code Petani ---
Route::get('/petani/{kode_petani}/qrcode', function ($kode_petani) {
    $petani = \App\Models\Petani::where('kode_petani', $kode_petani)->with('lahans')->firstOrFail();
    $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate($kode_petani);
    return view('petani-qrcode', compact('petani', 'qrCode'));
})->name('petani.qrcode');

// --- Google Auth Routes ---
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

// --- Dashboard internal (sementara tanpa auth untuk kemudahan ujicoba) ---
Route::group([], function () {
    Route::get('/petani', PetaniManager::class)->name('petani.index');
    Route::get('/batch-produksi', BatchProduksiManager::class)->name('batch.index');
    Route::get('/peta-sebaran', PetaSebaran::class)->name('peta.sebaran');
    Route::get('/rute-distribusi', RuteDistribusiManager::class)->name('rute.index');
});

// --- Dashboard per role ---
Route::middleware(['auth'])->group(function () {
    Route::get('/petani/dashboard', PetaniDashboard::class)->name('petani.dashboard');
    Route::get('/pengepul/dashboard', PengepulDashboard::class)->name('pengepul.dashboard');
    Route::get('/kub/dashboard', KubDashboard::class)->name('kub.dashboard');
});

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('peta.sebaran');
    }
    return redirect()->route('login');
})->name('home');

Route::get('/dashboard', function () {
    return redirect()->route('peta.sebaran');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
