<?php

namespace App\Filament\Resources\Petanis\Pages;

use App\Filament\Resources\Petanis\PetaniResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPetanis extends ListRecords
{
    protected static string $resource = PetaniResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
