<?php

namespace App\Filament\Resources\SetoranGulas\Pages;

use App\Filament\Resources\SetoranGulas\SetoranGulaResource;
use App\Models\HargaHarian;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSetoranGula extends EditRecord
{
    protected static string $resource = SetoranGulaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
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
}
