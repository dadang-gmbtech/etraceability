<?php

namespace App\Filament\Resources\Pengepuls\Pages;

use App\Filament\Resources\Pengepuls\PengepulResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPengepul extends EditRecord
{
    protected static string $resource = PengepulResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
