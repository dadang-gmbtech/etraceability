<?php

namespace App\Filament\Resources\Petanis\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PetaniForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Petani')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('kode_petani')
                                    ->label('Kode Petani')
                                    ->placeholder('Otomatis: PTN-0001')
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Kode unik untuk QR Code petani'),

                                TextInput::make('nama')
                                    ->label('Nama Lengkap')
                                    ->required(),

                                TextInput::make('no_hp')
                                    ->label('Nomor HP')
                                    ->tel(),

                                Toggle::make('aktif')
                                    ->label('Status Aktif')
                                    ->default(true),
                            ]),
                    ]),

                Section::make('Alamat')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('alamat')
                                    ->label('Alamat')
                                    ->columnSpan(3),

                                TextInput::make('desa')
                                    ->label('Desa / Kelurahan'),

                                TextInput::make('kecamatan')
                                    ->label('Kecamatan'),

                                TextInput::make('kabupaten')
                                    ->label('Kabupaten / Kota'),
                            ]),
                    ]),
            ]);
    }
}
