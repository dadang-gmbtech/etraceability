<?php

namespace App\Filament\Resources\SetoranGulas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;

class SetoranGulasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_setor')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('petani.kode_petani')
                    ->label('Kode')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('petani.nama')
                    ->label('Petani')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jenis_produk')
                    ->label('Produk')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'gula_semut' => 'Gula Semut',
                        'raw_sugar'  => 'Raw Sugar',
                        'nira'       => 'Nira',
                        'gula_cair'  => 'Gula Cair',
                        default      => $state,
                    }),

                TextColumn::make('berat_kg')
                    ->label('Berat (kg)')
                    ->numeric(2)
                    ->suffix(' kg')
                    ->sortable()
                    ->summarize([
                        Sum::make()->label('Total Berat (kg)'),
                    ]),

                TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->money('IDR')
                    ->sortable()
                    ->summarize([
                        Sum::make()->label('Total Pendapatan (Rp)')->money('IDR'),
                    ]),

                TextColumn::make('pengepul.nama_koperasi')
                    ->label('Via Pengepul')
                    ->placeholder('Langsung KUB')
                    ->toggleable(),

                TextColumn::make('batchProduksi.trace_id')
                    ->label('Batch')
                    ->badge()
                    ->color('success')
                    ->placeholder('—')
                    ->toggleable(),

                IconColumn::make('is_anomali')
                    ->label('⚠️ Anomali')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->filters([
                SelectFilter::make('petani_id')
                    ->label('Filter Petani')
                    ->relationship('petani', 'nama')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "[{$record->kode_petani}] {$record->nama}")
                    ->searchable()
                    ->preload(),

                SelectFilter::make('jenis_produk')
                    ->label('Jenis Produk')
                    ->options([
                        'gula_semut' => 'Gula Semut',
                        'raw_sugar'  => 'Raw Sugar',
                        'nira'       => 'Nira',
                        'gula_cair'  => 'Gula Cair',
                    ]),

                Filter::make('rentang_tanggal')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('dari')->label('Dari Tanggal'),
                        DatePicker::make('sampai')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari'], fn ($q) => $q->where('tanggal_setor', '>=', $data['dari']))
                            ->when($data['sampai'], fn ($q) => $q->where('tanggal_setor', '<=', $data['sampai']));
                    }),

                SelectFilter::make('is_anomali')
                    ->label('Status Anomali')
                    ->options([
                        '1' => '⚠️ Ada Anomali',
                        '0' => '✅ Normal',
                    ]),
            ])
            ->defaultSort('tanggal_setor', 'desc')
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
