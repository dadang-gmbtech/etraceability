<?php

namespace App\Filament\Resources\BatchProduksis\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BatchProduksisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trace_id')
                    ->label('Trace ID')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->copyable(),

                TextColumn::make('tanggal_pengumpulan')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('status_batch')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'dikumpulkan' => 'warning',
                        'diproses'    => 'info',
                        'selesai'     => 'success',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'dikumpulkan' => 'Dikumpulkan',
                        'diproses'    => 'Diproses',
                        'selesai'     => 'Selesai',
                        default       => ucfirst($state),
                    }),

                TextColumn::make('pengepul.nama_koperasi')
                    ->label('Pengepul')
                    ->placeholder('Langsung ke KUB')
                    ->searchable(),

                TextColumn::make('setoranGulas_count')
                    ->label('Setoran')
                    ->counts('setoranGulas')
                    ->suffix(' petani')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('berat_total_kg')
                    ->label('Berat Total')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->suffix(' kg'),

                IconColumn::make('is_organik')
                    ->label('Organik')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('lihat_traceability')
                    ->label('Traceability')
                    ->icon('heroicon-o-map-pin')
                    ->color('success')
                    ->url(fn ($record) => route('batch.traceability', $record->trace_id))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
