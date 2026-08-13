<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SettingsPage extends Page
{
    protected string $view = 'filament.pages.settings';

    public function getTitle(): string
    {
        return 'Pengaturan Sistem';
    }

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?int $navigationSort = 99;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'koefisien_normal_kg_per_pohon'      => Setting::getValue('koefisien_normal_kg_per_pohon', 0.5),
            'koefisien_max_kg_per_pohon'          => Setting::getValue('koefisien_max_kg_per_pohon', 0.75),
            'anomali_max_gula_semut_per_pohon'   => Setting::getValue('anomali_max_gula_semut_per_pohon', 0.75),
            'anomali_max_raw_sugar_per_pohon'    => Setting::getValue('anomali_max_raw_sugar_per_pohon', 0.75),
            'anomali_max_nira_per_pohon'         => Setting::getValue('anomali_max_nira_per_pohon', 2.0),
            'anomali_max_gula_cair_per_pohon'    => Setting::getValue('anomali_max_gula_cair_per_pohon', 1.5),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Batas Anomali Per Jenis Produk')
                    ->description('Jika setoran seorang petani melebihi nilai ini (kg atau liter per pohon per periode), sistem akan menandai setoran sebagai anomali. Hitung berdasarkan jumlah pohon di deres milik petani tersebut.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('anomali_max_gula_semut_per_pohon')
                                ->label('🍚 Gula Semut — Batas Anomali')
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0.01)
                                ->suffix('kg / pohon')
                                ->required()
                                ->helperText('Default: 0.75 kg/pohon. Setoran di atas nilai ini dianggap anomali.'),

                            TextInput::make('anomali_max_raw_sugar_per_pohon')
                                ->label('🔵 Raw Sugar — Batas Anomali')
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0.01)
                                ->suffix('kg / pohon')
                                ->required()
                                ->helperText('Default: 0.75 kg/pohon.'),

                            TextInput::make('anomali_max_nira_per_pohon')
                                ->label('💧 Nira — Batas Anomali')
                                ->numeric()
                                ->step(0.1)
                                ->minValue(0.1)
                                ->suffix('liter / pohon')
                                ->required()
                                ->helperText('Default: 2.0 liter/pohon. Nira lebih tinggi karena masih cairan.'),

                            TextInput::make('anomali_max_gula_cair_per_pohon')
                                ->label('🫙 Gula Cair — Batas Anomali')
                                ->numeric()
                                ->step(0.1)
                                ->minValue(0.1)
                                ->suffix('kg / pohon')
                                ->required()
                                ->helperText('Default: 1.5 kg/pohon.'),
                        ]),
                    ]),

                Section::make('Koefisien Produksi (Referensi)')
                    ->description('Nilai referensi produksi normal per pohon per hari. Digunakan sebagai acuan estimasi, bukan untuk deteksi anomali.')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('koefisien_normal_kg_per_pohon')
                                ->label('Produksi Normal (kg/pohon/hari)')
                                ->numeric()
                                ->step(0.01)
                                ->suffix('kg')
                                ->required()
                                ->helperText('Rata-rata produksi normal 1 pohon per hari. Default: 0.5 kg'),

                            TextInput::make('koefisien_max_kg_per_pohon')
                                ->label('Batas Umum (kg/pohon/hari) — Legacy')
                                ->numeric()
                                ->step(0.01)
                                ->suffix('kg')
                                ->required()
                                ->helperText('Nilai lama — kini digantikan pengaturan per jenis produk di atas.'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Notification::make()
            ->success()
            ->title('Pengaturan berhasil disimpan')
            ->send();
    }
}
