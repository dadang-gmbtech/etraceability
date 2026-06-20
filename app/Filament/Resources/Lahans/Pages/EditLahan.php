<?php

namespace App\Filament\Resources\Lahans\Pages;

use App\Filament\Resources\Lahans\LahanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLahan extends EditRecord
{
    protected static string $resource = LahanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // koordinat sudah di-cast sebagai array di model, tinggal encode untuk textarea
        if (isset($data['koordinat']) && is_array($data['koordinat'])) {
            $data['koordinat'] = json_encode($data['koordinat']);
        }
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['koordinat']) && is_string($data['koordinat'])) {
            $decoded = json_decode($data['koordinat'], true);
            $data['koordinat'] = $decoded;
        }
        unset($data['koordinat_map']);
        return $data;
    }
}
