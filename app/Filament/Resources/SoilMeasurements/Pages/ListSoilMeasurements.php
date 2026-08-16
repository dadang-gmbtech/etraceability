<?php

namespace App\Filament\Resources\SoilMeasurements\Pages;

use App\Filament\Resources\SoilMeasurements\SoilMeasurementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSoilMeasurements extends ListRecords
{
    protected static string $resource = SoilMeasurementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
