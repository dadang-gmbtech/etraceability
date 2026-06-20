<?php

namespace App\Filament\Resources\Petanis\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PetanisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_petani')
                    ->label('Kode')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('nama')
                    ->label('Nama Petani')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable(),
                TextColumn::make('lahans_count')
                    ->label('Jumlah Lahan')
                    ->counts('lahans')
                    ->suffix(' lahan')
                    ->sortable(),
                TextColumn::make('total_pohon')
                    ->label('Total Pohon')
                    ->getStateUsing(fn ($record) => $record->lahans()->sum('jumlah_pohon'))
                    ->suffix(' pohon'),
                TextColumn::make('desa')
                    ->label('Desa')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('kecamatan')
                    ->label('Kecamatan')
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('cetak_qr')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->url(fn ($record) => route('petani.qrcode', $record->kode_petani))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
