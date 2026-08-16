<?php

namespace App\Filament\Resources\SoilMeasurements\Pages;

use App\Filament\Resources\SoilMeasurements\SoilMeasurementResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSoilMeasurement extends CreateRecord
{
    protected static string $resource = SoilMeasurementResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $measuredAt = $data['measured_at'] ?? null;
        unset($data['measured_at']);

        $record = parent::handleRecordCreation($data);

        if ($measuredAt) {
            $record->forceFill(['created_at' => $measuredAt])->saveQuietly();
        }

        return $record;
    }
}
