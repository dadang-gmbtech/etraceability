<?php

namespace App\Filament\Resources\Lahans\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class LahanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Informasi Lahan ───────────────────────────────────────
                Section::make('Informasi Lahan')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('kode_lahan')
                                ->label('Kode Lahan')
                                ->required()
                                ->maxLength(9)
                                ->alphaDash()
                                ->placeholder('Maks. 9 karakter'),

                            TextInput::make('pemilik')
                                ->label('Pemilik Lahan')
                                ->placeholder('Nama pemilik lahan'),

                            TextInput::make('blok_lahan')
                                ->label('Blok Lahan')
                                ->placeholder('Contoh: A, B, C'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('desa')
                                ->label('Desa')
                                ->placeholder('Nama desa'),

                            Select::make('jenis_geometri')
                                ->label('Jenis Geometri')
                                ->options([
                                    'titik'   => '📍 Titik',
                                    'polygon' => '📐 Polygon',
                                ])
                                ->default('polygon')
                                ->required(),
                        ]),
                    ]),

                // ── Data Produksi ─────────────────────────────────────────
                Section::make('Data Produksi')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('luas_lahan')
                                ->label('Luas Lahan (ha)')
                                ->numeric()
                                ->minValue(0)
                                ->step(0.0001)
                                ->placeholder('0.0000')
                                ->suffix('ha'),

                            TextInput::make('pohon_di_deres')
                                ->label('Pohon di Deres')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->suffix('pohon'),

                            TextInput::make('kelapa_buah')
                                ->label('Kelapa Buah')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->suffix('buah'),
                        ]),
                    ]),

                // ── Koordinat Tersimpan ───────────────────────────────────
                Section::make('Koordinat Tersimpan')
                    ->schema([
                        Placeholder::make('koordinat_status')
                            ->label('Data Koordinat')
                            ->content(function ($record): HtmlString {
                                $geom = $record?->koordinat;
                                if (empty($geom)) {
                                    return new HtmlString(
                                        '<span class="text-sm text-gray-400 italic">Belum ada koordinat tersimpan.</span>'
                                    );
                                }

                                $type = $geom['type'] ?? 'Unknown';

                                if ($type === 'Point') {
                                    [$lng, $lat] = $geom['coordinates'];
                                    $html = '<div class="text-sm space-y-1">'
                                        . '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">📍 Titik (Point)</span>'
                                        . '<p class="text-gray-700 dark:text-gray-300 font-mono mt-1">Latitude: <b>' . round($lat, 7) . '</b></p>'
                                        . '<p class="text-gray-700 dark:text-gray-300 font-mono">Longitude: <b>' . round($lng, 7) . '</b></p>'
                                        . '</div>';
                                } elseif ($type === 'Polygon') {
                                    $ring   = $geom['coordinates'][0] ?? [];
                                    $count  = max(0, count($ring) - 1);
                                    $html = '<div class="text-sm space-y-1">'
                                        . '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">📐 Polygon</span>'
                                        . '<p class="text-gray-700 dark:text-gray-300 mt-1">' . $count . ' titik sudut tersimpan</p>'
                                        . '</div>';
                                } elseif ($type === 'FeatureCollection') {
                                    $count = count($geom['features'] ?? []);
                                    $html = '<div class="text-sm space-y-1">'
                                        . '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">🗺️ FeatureCollection</span>'
                                        . '<p class="text-gray-700 dark:text-gray-300 mt-1">' . $count . ' fitur tersimpan</p>'
                                        . '</div>';
                                } elseif ($type === 'MultiPolygon') {
                                    $count = count($geom['coordinates'] ?? []);
                                    $html = '<div class="text-sm space-y-1">'
                                        . '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">📐 MultiPolygon</span>'
                                        . '<p class="text-gray-700 dark:text-gray-300 mt-1">' . $count . ' polygon tersimpan</p>'
                                        . '</div>';
                                } else {
                                    $html = '<span class="text-sm text-gray-500">Tipe: ' . e($type) . '</span>';
                                }

                                return new HtmlString($html);
                            }),

                        Actions::make([
                            Action::make('hapusKoordinat')
                                ->label('Hapus Koordinat')
                                ->color('danger')
                                ->icon('heroicon-o-trash')
                                ->requiresConfirmation()
                                ->modalHeading('Hapus Koordinat?')
                                ->modalDescription('Data koordinat lahan ini akan dihapus. Anda bisa menggambar ulang lewat peta di bawah.')
                                ->modalSubmitActionLabel('Ya, Hapus')
                                ->action(function ($record): void {
                                    if ($record) {
                                        $record->update(['koordinat' => null]);
                                    }
                                }),
                        ]),
                    ])
                    ->columnSpanFull(),

                // ── Peta Lokasi Lahan ─────────────────────────────────────
                Section::make('📍 Peta Lokasi Lahan')
                    ->description('Klik ikon pada peta untuk menandai lokasi. Gunakan marker untuk titik atau polygon untuk area lahan.')
                    ->schema([
                        Hidden::make('koordinat'),
                        ViewField::make('koordinat_map')
                            ->view('filament.forms.components.lahan-map-field')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
