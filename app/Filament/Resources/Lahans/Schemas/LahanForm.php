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
                Grid::make(2)
                    ->schema([
                        TextInput::make('nama_lahan')
                            ->label('Nama Lahan')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('pemilik')
                            ->label('Pemilik Lahan')
                            ->placeholder('Nama pemilik lahan')
                            ->columnSpan(1),

                        TextInput::make('jumlah_pohon')
                            ->label('Jumlah Pohon Kelapa')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->suffix('pohon')
                            ->columnSpan(1),

                        Select::make('jenis_geometri')
                            ->label('Jenis Geometri Lahan')
                            ->options([
                                'titik'   => '📍 Titik — lokasi tidak diketahui batasnya',
                                'polygon' => '📐 Polygon — batas lahan diketahui',
                            ])
                            ->default('polygon')
                            ->required()
                            ->columnSpan(1),
                    ]),

                Section::make('📍 Peta Lokasi Lahan')
                    ->description('Klik ikon pada peta untuk menandai lokasi. Gunakan marker untuk titik atau polygon untuk area lahan.')
                    ->schema([
                        ViewField::make('koordinat_map')
                            ->view('filament.forms.components.lahan-map-field')
                            ->columnSpanFull()
                            ->dehydrated(false),

                        Hidden::make('koordinat'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
