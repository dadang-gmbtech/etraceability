<?php

namespace App\Filament\Resources\HargaHarians\Pages;

use App\Filament\Resources\HargaHarians\HargaHarianResource;
use App\Models\HargaHarian;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\HargaHarians\Widgets\HargaHarianChartWidget;
use Filament\Schemas\Components\Grid;

class ListHargaHarians extends ListRecords
{
    protected static string $resource = HargaHarianResource::class;

    protected function getFooterWidgets(): array
    {
        return [
            HargaHarianChartWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('atur_harga')
                ->label('Atur Harga Harian')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->modalHeading('Atur Harga Produk')
                ->modalDescription('Tetapkan harga untuk semua jenis produk pada tanggal yang dipilih. Jika sudah ada data, nilai akan diperbarui.')
                ->modalWidth('lg')
                ->form([
                    DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if (! $state) return;
                            $harga = HargaHarian::where('tanggal', $state)
                                ->pluck('harga_per_kg', 'jenis_produk');
                            $set('harga_gula_semut', $harga['gula_semut'] ?? null);
                            $set('harga_raw_sugar',  $harga['raw_sugar']  ?? null);
                            $set('harga_nira',        $harga['nira']       ?? null);
                            $set('harga_gula_cair',   $harga['gula_cair']  ?? null);
                        }),

                    Grid::make(2)->schema([
                        TextInput::make('harga_gula_semut')
                            ->label('🍚 Gula Semut')
                            ->helperText('Rp per kg')
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->required(),

                        TextInput::make('harga_raw_sugar')
                            ->label('🔵 Raw Sugar')
                            ->helperText('Rp per kg')
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->required(),

                        TextInput::make('harga_nira')
                            ->label('💧 Nira')
                            ->helperText('Rp per liter')
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->required(),

                        TextInput::make('harga_gula_cair')
                            ->label('🫙 Gula Cair')
                            ->helperText('Rp per kg')
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->required(),
                    ]),
                ])
                ->fillForm(function (): array {
                    $tanggal = today()->toDateString();
                    $harga   = HargaHarian::where('tanggal', $tanggal)
                        ->pluck('harga_per_kg', 'jenis_produk');

                    // Jika hari ini belum ada, salin dari hari sebelumnya yang punya data
                    if ($harga->isEmpty()) {
                        $harga = HargaHarian::where('tanggal', '<', $tanggal)
                            ->orderByDesc('tanggal')
                            ->get()
                            ->groupBy('jenis_produk')
                            ->map(fn ($rows) => $rows->first()->harga_per_kg);
                    }

                    return [
                        'tanggal'          => $tanggal,
                        'harga_gula_semut' => $harga['gula_semut'] ?? null,
                        'harga_raw_sugar'  => $harga['raw_sugar']  ?? null,
                        'harga_nira'       => $harga['nira']       ?? null,
                        'harga_gula_cair'  => $harga['gula_cair']  ?? null,
                    ];
                })
                ->action(function (array $data): void {
                    $tanggal = $data['tanggal'];
                    $produk  = [
                        'gula_semut' => $data['harga_gula_semut'],
                        'raw_sugar'  => $data['harga_raw_sugar'],
                        'nira'       => $data['harga_nira'],
                        'gula_cair'  => $data['harga_gula_cair'],
                    ];

                    foreach ($produk as $jenis => $harga) {
                        HargaHarian::updateOrCreate(
                            ['tanggal' => $tanggal, 'jenis_produk' => $jenis],
                            ['harga_per_kg' => $harga]
                        );
                    }

                    Notification::make()
                        ->success()
                        ->title('Harga berhasil disimpan')
                        ->body("4 produk untuk tanggal {$tanggal} telah diperbarui.")
                        ->send();
                }),
        ];
    }
}
