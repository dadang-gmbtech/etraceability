<?php

namespace App\Filament\Resources\BatchProduksis\Schemas;

use App\Models\Pengepul;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BatchProduksiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Batch')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('trace_id')
                                    ->label('Trace ID')
                                    ->placeholder('Auto-generate jika kosong')
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Format: GKO-YYYYMMDD-0001'),

                                DatePicker::make('tanggal_pengumpulan')
                                    ->label('Tanggal Pengumpulan')
                                    ->default(now()),

                                Select::make('status_batch')
                                    ->label('Status Batch')
                                    ->options([
                                        'dikumpulkan' => '📦 Dikumpulkan',
                                        'diproses'    => '⚙️ Diproses',
                                        'selesai'     => '✅ Selesai',
                                    ])
                                    ->default('dikumpulkan')
                                    ->required(),

                                Toggle::make('is_organik')
                                    ->label('Produk Organik')
                                    ->default(true),
                            ]),
                    ]),

                Section::make('Distribusi & Berat')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('pengepul_id')
                                    ->label('Pengepul (Opsional)')
                                    ->relationship('pengepul', 'nama_koperasi')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->placeholder('Langsung ke KUB'),

                                TextInput::make('berat_total_kg')
                                    ->label('Berat Total (kg)')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('kg')
                                    ->helperText('Otomatis dihitung dari total setoran'),
                            ]),
                    ]),
            ]);
    }
}
