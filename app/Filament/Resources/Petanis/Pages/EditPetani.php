<?php

namespace App\Filament\Resources\Petanis\Pages;

use App\Filament\Resources\Petanis\PetaniResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPetani extends EditRecord
{
    protected static string $resource = PetaniResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
