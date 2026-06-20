<?php

namespace App\Filament\Resources\Pengepuls;

use App\Filament\Resources\Pengepuls\Pages\CreatePengepul;
use App\Filament\Resources\Pengepuls\Pages\EditPengepul;
use App\Filament\Resources\Pengepuls\Pages\ListPengepuls;
use App\Filament\Resources\Pengepuls\Schemas\PengepulForm;
use App\Filament\Resources\Pengepuls\Tables\PengepulsTable;
use App\Models\Pengepul;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PengepulResource extends Resource
{
    protected static ?string $model = Pengepul::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PengepulForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengepulsTable::configure($table);
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
            'index' => ListPengepuls::route('/'),
            'create' => CreatePengepul::route('/create'),
            'edit' => EditPengepul::route('/{record}/edit'),
        ];
    }
}
