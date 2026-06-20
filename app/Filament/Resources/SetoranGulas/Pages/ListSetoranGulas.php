<?php

namespace App\Filament\Resources\SetoranGulas\Pages;

use App\Filament\Resources\SetoranGulas\SetoranGulaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSetoranGulas extends ListRecords
{
    protected static string $resource = SetoranGulaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
