<?php

namespace App\Filament\Resources\HargaHarians\Pages;

use App\Filament\Resources\HargaHarians\HargaHarianResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHargaHarian extends EditRecord
{
    protected static string $resource = HargaHarianResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
