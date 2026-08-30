<?php

namespace App\Filament\Resources\BatasWilayahs;

use App\Filament\Resources\BatasWilayahs\Pages\ListBatasWilayahs;
use App\Models\BatasWilayah;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BatasWilayahResource extends Resource
{
    protected static ?string $model = BatasWilayah::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map';
    protected static string|\UnitEnum|null $navigationGroup = 'Peta & Wilayah';
    protected static ?string $navigationLabel  = 'Batas Wilayah';
    protected static ?string $pluralModelLabel = 'Batas Wilayah';
    protected static ?string $modelLabel       = 'Batas Wilayah';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'kecamatan' => 'info',
                        'desa'      => 'success',
                        default     => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('jenis')
            ->striped()
            ->actions([
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBatasWilayahs::route('/'),
        ];
    }
}
