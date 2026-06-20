<?php

namespace App\Filament\Resources\Devices;

use App\Filament\Resources\Devices\Pages\CreateDevice;
use App\Filament\Resources\Devices\Pages\EditDevice;
use App\Filament\Resources\Devices\Pages\ListDevices;
use App\Models\Device;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?string $navigationLabel = 'Perangkat IoT';

    public static function getNavigationGroup(): ?string
    {
        return 'IoT & Monitoring';
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Perangkat')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Nama Perangkat')
                            ->required(),

                        Select::make('lahan_id')
                            ->label('Lahan (Lokasi Sensor)')
                            ->relationship('lahan', 'nama_lahan')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nama_lahan} ({$record->petani?->nama})")
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('— Pilih Lahan —'),

                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->nullable(),

                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->nullable(),

                        Toggle::make('status')
                            ->label('Status Aktif')
                            ->onColor('success')
                            ->offColor('danger')
                            ->formatStateUsing(fn ($state) => $state === 'active')
                            ->dehydrateStateUsing(fn ($state) => $state ? 'active' : 'inactive'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Perangkat')
                    ->searchable(),

                TextColumn::make('lahan.nama_lahan')
                    ->label('Lahan')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('lahan.petani.nama')
                    ->label('Petani')
                    ->placeholder('—'),

                TextColumn::make('latitude')
                    ->label('Lat')
                    ->numeric(decimalPlaces: 6)
                    ->toggleable(),

                TextColumn::make('longitude')
                    ->label('Lng')
                    ->numeric(decimalPlaces: 6)
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state === 'active' ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => $state === 'active' ? 'Aktif' : 'Nonaktif'),

                TextColumn::make('soilMeasurements_count')
                    ->label('Pengukuran')
                    ->counts('soilMeasurements')
                    ->suffix('x'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDevices::route('/'),
            'create' => CreateDevice::route('/create'),
            'edit'   => EditDevice::route('/{record}/edit'),
        ];
    }
}
