<?php

namespace App\Filament\Resources\Petanis\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LahansRelationManager extends RelationManager
{
    protected static string $relationship = 'lahans';

    protected static ?string $title = 'Lahan yang Dikelola';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_lahan')
            ->columns([
                TextColumn::make('nama_lahan')
                    ->label('Nama Lahan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pemilik')
                    ->label('Pemilik Lahan')
                    ->default('—'),

                TextColumn::make('jenis_geometri')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn ($state) => $state === 'titik' ? 'info' : 'success')
                    ->formatStateUsing(fn ($state) => $state === 'titik' ? '📍 Titik' : '📐 Polygon'),

                TextColumn::make('jumlah_pohon')
                    ->label('Jumlah Pohon')
                    ->suffix(' pohon')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('koordinat')
                    ->label('Peta')
                    ->formatStateUsing(fn ($state) => $state ? '✓ Ada' : '—')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])
            ->headerActions([
                AssociateAction::make()
                    ->label('Tambahkan Lahan')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['nama_lahan', 'pemilik'])
                    ->recordSelectOptionsQuery(fn ($query) => $query->whereNull('petani_id'))
                    ->recordTitle(fn ($record) => "{$record->nama_lahan}" . ($record->pemilik ? " (pemilik: {$record->pemilik})" : '') . " — {$record->jumlah_pohon} pohon"),
            ])
            ->actions([
                DissociateAction::make()
                    ->label('Lepaskan')
                    ->modalHeading('Lepaskan lahan dari petani ini?')
                    ->modalDescription('Lahan tidak akan dihapus, hanya dilepas dari petani ini.'),
            ])
            ->bulkActions([
                DissociateBulkAction::make()
                    ->label('Lepaskan yang dipilih'),
            ]);
    }
}
