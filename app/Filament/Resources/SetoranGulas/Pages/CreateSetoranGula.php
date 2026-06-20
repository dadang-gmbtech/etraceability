<?php

namespace App\Filament\Resources\SetoranGulas\Pages;

use App\Filament\Resources\SetoranGulas\SetoranGulaResource;
use App\Models\BatchProduksi;
use App\Models\HargaHarian;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSetoranGula extends CreateRecord
{
    protected static string $resource = SetoranGulaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan total_harga selalu terisi (kalau form tidak update otomatis)
        if (empty($data['total_harga'])) {
            $harga = HargaHarian::where('tanggal', $data['tanggal_setor'] ?? today())
                ->where('jenis_produk', $data['jenis_produk'] ?? '')
                ->value('harga_per_kg');

            $data['total_harga'] = $harga
                ? round((float)($data['berat_kg'] ?? 0) * (float)$harga, 2)
                : 0;
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Jika belum ada batch, auto-buat atau cari batch hari ini yang masih terbuka
        if (empty($data['batch_produksi_id'])) {
            $today = now()->toDateString();

            $batch = BatchProduksi::firstOrCreate(
                [
                    'tanggal_pengumpulan' => $today,
                    'status_batch'        => 'dikumpulkan',
                ],
                [
                    'trace_id'       => BatchProduksi::generateTraceId(),
                    'is_organik'     => false,
                    'berat_total_kg' => 0,
                ]
            );

            $data['batch_produksi_id'] = $batch->id;
        }

        $setoran = parent::handleRecordCreation($data);

        // Update total berat batch
        if ($setoran->batch_produksi_id) {
            $totalBerat = $setoran->batchProduksi
                ->setoranGulas()
                ->sum('berat_kg');

            $setoran->batchProduksi->update(['berat_total_kg' => $totalBerat]);
        }

        return $setoran;
    }
}
