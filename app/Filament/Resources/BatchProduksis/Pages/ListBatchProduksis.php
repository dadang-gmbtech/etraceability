<?php

namespace App\Filament\Resources\BatchProduksis\Pages;

use App\Filament\Resources\BatchProduksis\BatchProduksiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBatchProduksis extends ListRecords
{
    protected static string $resource = BatchProduksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
