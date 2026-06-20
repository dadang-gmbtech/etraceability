<?php

namespace App\Filament\Resources\SertifikasiOrganiks\Pages;

use App\Filament\Resources\SertifikasiOrganiks\SertifikasiOrganikResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSertifikasiOrganiks extends ListRecords
{
    protected static string $resource = SertifikasiOrganikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
