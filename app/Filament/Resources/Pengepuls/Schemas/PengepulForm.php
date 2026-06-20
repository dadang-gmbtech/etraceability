<?php

namespace App\Filament\Resources\Pengepuls\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PengepulForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_pengepul')
                    ->required(),
                TextInput::make('nama_koperasi')
                    ->required(),
                TextInput::make('penanggung_jawab'),
                TextInput::make('no_hp'),
                TextInput::make('alamat'),
                TextInput::make('lokasi_gudang'),
            ]);
    }
}
