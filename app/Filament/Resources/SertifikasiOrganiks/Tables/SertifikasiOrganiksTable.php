<?php

namespace App\Filament\Resources\SertifikasiOrganiks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SertifikasiOrganiksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('petani_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nomor_sertifikat')
                    ->searchable(),
                TextColumn::make('lembaga_sertifikasi')
                    ->searchable(),
                TextColumn::make('tanggal_terbit')
                    ->date()
                    ->sortable(),
                TextColumn::make('tanggal_kadaluarsa')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('file_dokumen')
                    ->searchable(),
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
