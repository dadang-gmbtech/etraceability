<?php

namespace App\Filament\Resources\SetoranGulas;

use App\Filament\Resources\SetoranGulas\Pages\CreateSetoranGula;
use App\Filament\Resources\SetoranGulas\Pages\EditSetoranGula;
use App\Filament\Resources\SetoranGulas\Pages\ListSetoranGulas;
use App\Filament\Resources\SetoranGulas\Schemas\SetoranGulaForm;
use App\Filament\Resources\SetoranGulas\Tables\SetoranGulasTable;
use App\Models\SetoranGula;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SetoranGulaResource extends Resource
{
    protected static ?string $model = SetoranGula::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Setoran Produk';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return SetoranGulaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SetoranGulasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSetoranGulas::route('/'),
            'create' => CreateSetoranGula::route('/create'),
            'edit'   => EditSetoranGula::route('/{record}/edit'),
        ];
    }
}
