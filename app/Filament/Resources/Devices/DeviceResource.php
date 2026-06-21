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
                            ->live()
                            ->placeholder('— Pilih Lahan —')
                            ->afterStateUpdated(function ($state, $set) {
                                if (!$state) return;
                                $lahan = \App\Models\Lahan::find($state);
                                if (!$lahan || !$lahan->koordinat) return;
                                $geom = is_array($lahan->koordinat)
                                    ? $lahan->koordinat
                                    : json_decode($lahan->koordinat, true);
                                [$lat, $lng] = static::hitungCentroid($geom);
                                if ($lat !== null) {
                                    $set('latitude',  round($lat, 7));
                                    $set('longitude', round($lng, 7));
                                }
                            }),

                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->nullable()
                            ->readOnly(fn ($get) => (bool) $get('lahan_id'))
                            ->helperText(fn ($get) => $get('lahan_id')
                                ? '📍 Diisi otomatis dari titik tengah lahan'
                                : 'Isi manual jika perangkat berada di luar lahan'),

                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->nullable()
                            ->readOnly(fn ($get) => (bool) $get('lahan_id'))
                            ->helperText(fn ($get) => $get('lahan_id')
                                ? '📍 Diisi otomatis dari titik tengah lahan'
                                : 'Isi manual jika perangkat berada di luar lahan'),

                        Toggle::make('status')
                            ->label('Status Aktif')
                            ->onColor('success')
                            ->offColor('danger')
                            ->formatStateUsing(fn ($state) => $state === 'active')
                            ->dehydrateStateUsing(fn ($state) => $state ? 'active' : 'inactive'),
                    ]),
                ]),

            Section::make('Token API Perangkat')
                ->description('Token ini digunakan perangkat IoT untuk mengirim data sensor ke server. Simpan dan jaga kerahasiaannya.')
                ->collapsed()
                ->schema([
                    TextInput::make('api_token')
                        ->label('Token API')
                        ->readOnly()
                        ->helperText('Gunakan token ini di header HTTP: X-Device-Token: {token}')
                        ->suffixAction(
                            \Filament\Actions\Action::make('salin_token')
                                ->icon(Heroicon::OutlinedClipboard)
                                ->label('Salin')
                                ->action(fn () => null)
                                ->extraAttributes(['x-on:click' => 'window.navigator.clipboard.writeText($el.closest(\'.fi-input-wrp\').querySelector(\'input\').value); $tooltip(\'Token disalin!\', { timeout: 1500 })']),
                        ),
                ]),
        ]);
    }

    private static function hitungCentroid(?array $geom): array
    {
        if (!$geom || !isset($geom['type'])) return [null, null];

        if ($geom['type'] === 'Point') {
            return [$geom['coordinates'][1], $geom['coordinates'][0]];
        }

        if ($geom['type'] === 'Polygon' && !empty($geom['coordinates'][0])) {
            $ring = $geom['coordinates'][0];
            // Hapus titik penutup jika sama dengan titik pertama
            if (count($ring) > 1 && $ring[0] === end($ring)) {
                array_pop($ring);
            }
            $lats = array_column($ring, 1);
            $lngs = array_column($ring, 0);
            $n    = count($ring);
            return [array_sum($lats) / $n, array_sum($lngs) / $n];
        }

        return [null, null];
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
