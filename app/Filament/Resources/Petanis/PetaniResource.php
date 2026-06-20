<?php

namespace App\Filament\Resources\Petanis;

use App\Filament\Resources\Petanis\Pages\CreatePetani;
use App\Filament\Resources\Petanis\Pages\EditPetani;
use App\Filament\Resources\Petanis\Pages\ListPetanis;
use App\Filament\Resources\Petanis\RelationManagers\LahansRelationManager;
use App\Filament\Resources\Petanis\Schemas\PetaniForm;
use App\Filament\Resources\Petanis\Tables\PetanisTable;
use App\Models\Petani;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PetaniResource extends Resource
{
    protected static ?string $model = Petani::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'nama';

    protected static ?string $navigationLabel = 'Data Petani';

    public static function form(Schema $schema): Schema
    {
        return PetaniForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PetanisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LahansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPetanis::route('/'),
            'create' => CreatePetani::route('/create'),
            'edit' => EditPetani::route('/{record}/edit'),
        ];
    }
}
