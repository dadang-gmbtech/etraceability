<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Form;
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
            'koefisien_normal_kg_per_pohon' => Setting::getValue('koefisien_normal_kg_per_pohon', 0.5),
            'koefisien_max_kg_per_pohon'    => Setting::getValue('koefisien_max_kg_per_pohon', 0.75),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Koefisien Produksi Gula Kelapa')
                    ->description('Konfigurasi batas produksi per pohon per hari. Digunakan untuk mendeteksi anomali setoran yang tidak wajar.')
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
                                ->label('Batas Anomali (kg/pohon/hari)')
                                ->numeric()
                                ->step(0.01)
                                ->suffix('kg')
                                ->required()
                                ->helperText('Jika setoran melebihi nilai ini, sistem memunculkan peringatan anomali. Default: 0.75 kg'),
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
