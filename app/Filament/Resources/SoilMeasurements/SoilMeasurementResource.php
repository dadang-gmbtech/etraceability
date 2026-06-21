<?php

namespace App\Filament\Resources\SoilMeasurements;

use App\Filament\Resources\SoilMeasurements\Pages\ListSoilMeasurements;
use App\Models\SoilMeasurement;
use BackedEnum;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SoilMeasurementResource extends Resource
{
    protected static ?string $model = SoilMeasurement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?string $navigationLabel = 'Data Sensor Tanah';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'IoT & Monitoring';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),

                TextColumn::make('device.name')
                    ->label('Perangkat')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('device.lahan.nama_lahan')
                    ->label('Lahan')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('ph_level')
                    ->label('pH Tanah')
                    ->numeric(decimalPlaces: 2)
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state, 2) : '—')
                    ->color(fn ($state) => match (true) {
                        $state === null          => 'gray',
                        $state < 5.5 || $state > 7.5 => 'danger',
                        $state < 6.0 || $state > 7.0 => 'warning',
                        default                  => 'success',
                    }),

                TextColumn::make('moisture')
                    ->label('Kelembaban (%)')
                    ->numeric(decimalPlaces: 2)
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state, 2).'%' : '—')
                    ->color(fn ($state) => match (true) {
                        $state === null            => 'gray',
                        $state < 40 || $state > 90 => 'warning',
                        default                    => 'success',
                    }),

                TextColumn::make('temperature')
                    ->label('Suhu (°C)')
                    ->numeric(decimalPlaces: 2)
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state, 2).' °C' : '—'),

                TextColumn::make('nitrogen')
                    ->label('N (mg/kg)')
                    ->numeric(decimalPlaces: 2)
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state, 2) : '—'),

                TextColumn::make('phosphorus')
                    ->label('P (mg/kg)')
                    ->numeric(decimalPlaces: 2)
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state, 2) : '—'),

                TextColumn::make('potassium')
                    ->label('K (mg/kg)')
                    ->numeric(decimalPlaces: 2)
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state, 2) : '—'),
            ])
            ->filters([
                SelectFilter::make('device_id')
                    ->label('Perangkat')
                    ->relationship('device', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSoilMeasurements::route('/'),
        ];
    }
}
