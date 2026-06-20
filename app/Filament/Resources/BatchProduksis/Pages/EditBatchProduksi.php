<?php

namespace App\Filament\Resources\BatchProduksis\Pages;

use App\Filament\Resources\BatchProduksis\BatchProduksiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBatchProduksi extends EditRecord
{
    protected static string $resource = BatchProduksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
