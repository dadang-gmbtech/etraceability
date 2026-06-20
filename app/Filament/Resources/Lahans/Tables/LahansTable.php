<?php

namespace App\Filament\Resources\Lahans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LahansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_lahan')
                    ->label('Nama Lahan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pemilik')
                    ->label('Pemilik Lahan')
                    ->default('—')
                    ->searchable(),
                TextColumn::make('petani.nama')
                    ->label('Dikelola Petani')
                    ->default('—')
                    ->searchable(),
                TextColumn::make('jumlah_pohon')
                    ->label('Jumlah Pohon')
                    ->numeric()
                    ->sortable()
                    ->suffix(' pohon'),
                TextColumn::make('jenis_geometri')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'titik'   => 'info',
                        'polygon' => 'success',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'titik'   => '📍 Titik',
                        'polygon' => '📐 Polygon',
                        default   => $state,
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
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
