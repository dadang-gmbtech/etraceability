<?php

namespace App\Filament\Resources\SoilMeasurements\Pages;

use App\Filament\Resources\SoilMeasurements\SoilMeasurementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSoilMeasurement extends EditRecord
{
    protected static string $resource = SoilMeasurementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Isi field virtual measured_at dari created_at record
        $data['measured_at'] = $this->record->created_at?->format('Y-m-d H:i:s');
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $measuredAt = $data['measured_at'] ?? null;
        unset($data['measured_at']);

        parent::handleRecordUpdate($record, $data);

        if ($measuredAt) {
            $record->forceFill(['created_at' => $measuredAt])->saveQuietly();
        }

        return $record;
    }
}
