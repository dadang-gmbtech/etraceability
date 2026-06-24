<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        \Illuminate\Support\Facades\Log::info('CreateUser data before create', \Illuminate\Support\Arr::except($data, ['password']));
        return parent::mutateFormDataBeforeCreate($data);
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            $record = parent::handleRecordCreation($data);
            \Illuminate\Support\Facades\Log::info('CreateUser success, id=' . $record->getKey());
            return $record;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('CreateUser handleRecordCreation error: ' . $e->getMessage());
            throw $e;
        }
    }
}
