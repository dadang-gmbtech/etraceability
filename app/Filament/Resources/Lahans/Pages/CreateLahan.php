<?php

namespace App\Filament\Resources\Lahans\Pages;

use App\Filament\Resources\Lahans\LahanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLahan extends CreateRecord
{
    protected static string $resource = LahanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan koordinat disimpan sebagai JSON yang valid
        if (isset($data['koordinat']) && is_string($data['koordinat'])) {
            $decoded = json_decode($data['koordinat'], true);
            $data['koordinat'] = $decoded;
        }
        
        // Hapus field dummy map view
        unset($data['koordinat_map']);
        
        return $data;
    }
}
