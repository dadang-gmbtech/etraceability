<?php

namespace App\Filament\Resources\Lahans\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LahanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Lahan')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('kode_lahan')
                                ->label('Kode Lahan')
                                ->required()
                                ->maxLength(9)
                                ->alphaDash()
                                ->placeholder('Maks. 9 karakter'),

                            TextInput::make('pemilik')
                                ->label('Pemilik Lahan')
                                ->placeholder('Nama pemilik lahan'),

                            TextInput::make('blok_lahan')
                                ->label('Blok Lahan')
                                ->placeholder('Contoh: A1, B2'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('desa')
                                ->label('Desa')
                                ->placeholder('Nama desa'),

                            Select::make('jenis_geometri')
                                ->label('Jenis Geometri')
                                ->options([
                                    'titik'   => '📍 Titik',
                                    'polygon' => '📐 Polygon',
                                ])
                                ->default('polygon')
                                ->required(),
                        ]),
                    ]),

                Section::make('Data Produksi')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('luas_lahan')
                                ->label('Luas Lahan (ha)')
                                ->numeric()
                                ->minValue(0)
                                ->step(0.0001)
                                ->placeholder('0.0000')
                                ->suffix('ha'),

                            TextInput::make('pohon_di_deres')
                                ->label('Pohon di Deres')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->suffix('pohon'),

                            TextInput::make('kelapa_buah')
                                ->label('Kelapa Buah')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->suffix('buah'),
                        ]),
                    ]),

                Section::make('📍 Peta Lokasi Lahan')
                    ->description('Klik ikon pada peta untuk menandai lokasi. Gunakan marker untuk titik atau polygon untuk area lahan.')
                    ->schema([
                        Hidden::make('koordinat'),
                        ViewField::make('koordinat_map')
                            ->view('filament.forms.components.lahan-map-field')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
