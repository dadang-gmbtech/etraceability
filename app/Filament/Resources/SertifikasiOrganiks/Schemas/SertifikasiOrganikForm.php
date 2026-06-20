<?php

namespace App\Filament\Resources\SertifikasiOrganiks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SertifikasiOrganikForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('petani_id')
                    ->required()
                    ->numeric(),
                TextInput::make('nomor_sertifikat')
                    ->required(),
                TextInput::make('lembaga_sertifikasi')
                    ->required(),
                DatePicker::make('tanggal_terbit')
                    ->required(),
                DatePicker::make('tanggal_kadaluarsa')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('aktif'),
                TextInput::make('file_dokumen'),
            ]);
    }
}
