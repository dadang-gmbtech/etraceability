<?php

namespace App\Filament\Resources\HargaHarians;

use App\Filament\Resources\HargaHarians\Pages\CreateHargaHarian;
use App\Filament\Resources\HargaHarians\Pages\EditHargaHarian;
use App\Filament\Resources\HargaHarians\Pages\ListHargaHarians;
use App\Models\HargaHarian;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class HargaHarianResource extends Resource
{
    protected static ?string $model = HargaHarian::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;
    protected static ?string $navigationLabel = 'Harga Harian';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->default(today())
                    ->required(),
                Select::make('jenis_produk')
                    ->label('Jenis Produk')
                    ->options([
                        'gula_semut' => '🍚 Gula Semut',
                        'raw_sugar'  => '🔵 Raw Sugar',
                        'nira'       => '💧 Nira',
                        'gula_cair'  => '🫙 Gula Cair',
                    ])
                    ->required(),
                TextInput::make('harga_per_kg')
                    ->label('Harga per Kg (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('jenis_produk')
                    ->label('Produk')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'gula_semut' => '🍚 Gula Semut',
                        'raw_sugar'  => '🔵 Raw Sugar',
                        'nira'       => '💧 Nira',
                        'gula_cair'  => '🫙 Gula Cair',
                        default      => $state,
                    }),
                TextColumn::make('harga_per_kg')
                    ->label('Harga/kg')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('tanggal', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListHargaHarians::route('/'),
            'create' => CreateHargaHarian::route('/create'),
            'edit'   => EditHargaHarian::route('/{record}/edit'),
        ];
    }
}
