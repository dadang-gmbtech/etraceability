<?php

namespace App\Filament\Resources\SoilMeasurements\Schemas;

use App\Models\Device;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SoilMeasurementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Perangkat & Waktu')
                ->schema([
                    Grid::make(['default' => 1, 'md' => 2])->schema([
                        Select::make('device_id')
                            ->label('Perangkat IoT')
                            ->options(fn () => Device::with('lahan')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn ($d) => [
                                    $d->id => $d->name . ($d->lahan ? ' — ' . $d->lahan->kode_lahan : ''),
                                ]))
                            ->searchable()
                            ->required()
                            ->helperText('Pilih perangkat yang digunakan saat pengukuran'),

                        DateTimePicker::make('measured_at')
                            ->label('Waktu Pengukuran')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->helperText('Waktu saat pengukuran dilakukan'),
                    ]),
                ]),

            Section::make('Parameter Tanah')
                ->description('Isi nilai yang tersedia. Kolom yang tidak diukur boleh dikosongkan.')
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                ->schema([
                    TextInput::make('ph_level')
                        ->label('pH Tanah')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(14)
                        ->step(0.01)
                        ->placeholder('0.00')
                        ->helperText('Optimal: 5.5 – 7.5'),

                    TextInput::make('moisture')
                        ->label('Kelembaban')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->step(0.01)
                        ->suffix('%')
                        ->placeholder('0.00')
                        ->helperText('Optimal: 40 – 90%'),

                    TextInput::make('temperature')
                        ->label('Suhu Tanah')
                        ->numeric()
                        ->minValue(-10)
                        ->maxValue(80)
                        ->step(0.01)
                        ->suffix('°C')
                        ->placeholder('0.00'),

                    TextInput::make('nitrogen')
                        ->label('Nitrogen (N)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(999.99)
                        ->step(0.01)
                        ->suffix('mg/kg')
                        ->placeholder('0.00'),

                    TextInput::make('phosphorus')
                        ->label('Fosfor (P)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(999.99)
                        ->step(0.01)
                        ->suffix('mg/kg')
                        ->placeholder('0.00'),

                    TextInput::make('potassium')
                        ->label('Kalium (K)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(999.99)
                        ->step(0.01)
                        ->suffix('mg/kg')
                        ->placeholder('0.00'),
                ]),
        ]);
    }
}
