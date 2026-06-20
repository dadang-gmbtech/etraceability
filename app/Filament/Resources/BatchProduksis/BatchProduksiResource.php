<?php

namespace App\Filament\Resources\BatchProduksis;

use App\Filament\Resources\BatchProduksis\Pages\CreateBatchProduksi;
use App\Filament\Resources\BatchProduksis\Pages\EditBatchProduksi;
use App\Filament\Resources\BatchProduksis\Pages\ListBatchProduksis;
use App\Filament\Resources\BatchProduksis\Schemas\BatchProduksiForm;
use App\Filament\Resources\BatchProduksis\Tables\BatchProduksisTable;
use App\Models\BatchProduksi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BatchProduksiResource extends Resource
{
    protected static ?string $model = BatchProduksi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BatchProduksiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BatchProduksisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBatchProduksis::route('/'),
            'create' => CreateBatchProduksi::route('/create'),
            'edit' => EditBatchProduksi::route('/{record}/edit'),
        ];
    }
}
