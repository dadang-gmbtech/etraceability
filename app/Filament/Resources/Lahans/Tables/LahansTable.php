<?php

namespace App\Filament\Resources\Lahans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LahansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_lahan')
                    ->label('Kode Lahan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pemilik')
                    ->label('Pemilik Lahan')
                    ->default('—')
                    ->searchable(),

                TextColumn::make('blok_lahan')
                    ->label('Blok Lahan')
                    ->default('—')
                    ->searchable(),

                TextColumn::make('desa')
                    ->label('Desa')
                    ->default('—')
                    ->searchable(),

                TextColumn::make('koordinat')
                    ->label('Koordinat')
                    ->formatStateUsing(fn ($state) => $state ? '✓ Ada' : '—')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray'),

                TextColumn::make('petani.nama')
                    ->label('Penderes')
                    ->default('—')
                    ->searchable(),

                TextColumn::make('petani.kode_petani')
                    ->label('Kode Penderes')
                    ->default('—')
                    ->searchable(),

                TextColumn::make('luas_lahan')
                    ->label('Luas Lahan (ha)')
                    ->numeric(decimalPlaces: 4)
                    ->default('—')
                    ->sortable()
                    ->suffix(' ha'),

                TextColumn::make('pohon_di_deres')
                    ->label('Pohon di Deres')
                    ->numeric()
                    ->sortable()
                    ->suffix(' pohon'),

                TextColumn::make('kelapa_buah')
                    ->label('Kelapa Buah')
                    ->numeric()
                    ->sortable()
                    ->suffix(' buah'),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
