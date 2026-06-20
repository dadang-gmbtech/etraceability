<?php

namespace App\Filament\Resources\SertifikasiOrganiks;

use App\Filament\Resources\SertifikasiOrganiks\Pages\CreateSertifikasiOrganik;
use App\Filament\Resources\SertifikasiOrganiks\Pages\EditSertifikasiOrganik;
use App\Filament\Resources\SertifikasiOrganiks\Pages\ListSertifikasiOrganiks;
use App\Filament\Resources\SertifikasiOrganiks\Schemas\SertifikasiOrganikForm;
use App\Filament\Resources\SertifikasiOrganiks\Tables\SertifikasiOrganiksTable;
use App\Models\SertifikasiOrganik;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SertifikasiOrganikResource extends Resource
{
    protected static ?string $model = SertifikasiOrganik::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SertifikasiOrganikForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SertifikasiOrganiksTable::configure($table);
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
            'index' => ListSertifikasiOrganiks::route('/'),
            'create' => CreateSertifikasiOrganik::route('/create'),
            'edit' => EditSertifikasiOrganik::route('/{record}/edit'),
        ];
    }
}
