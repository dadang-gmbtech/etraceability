<?php

namespace App\Filament\Resources\SertifikasiOrganiks\Pages;

use App\Filament\Resources\SertifikasiOrganiks\SertifikasiOrganikResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSertifikasiOrganik extends EditRecord
{
    protected static string $resource = SertifikasiOrganikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
