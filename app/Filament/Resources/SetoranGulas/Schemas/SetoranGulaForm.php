<?php

namespace App\Filament\Resources\SetoranGulas\Schemas;

use App\Models\BatchProduksi;
use App\Models\HargaHarian;
use App\Models\Petani;
use App\Models\Setting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SetoranGulaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Setoran')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('petani_id')
                                    ->label('Petani (atau Scan QR Kode Petani)')
                                    ->relationship('petani', 'nama')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "[{$record->kode_petani}] {$record->nama}")
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, $set) => $set('_total_pohon', 
                                        $state ? Petani::find($state)?->lahans()->sum('jumlah_pohon') : 0
                                    )),

                                Select::make('jenis_produk')
                                    ->label('Jenis Produk')
                                    ->options([
                                        'gula_semut'  => '🍚 Gula Semut',
                                        'raw_sugar'   => '🔵 Raw Sugar',
                                        'nira'        => '💧 Nira',
                                        'gula_cair'   => '🫙 Gula Cair',
                                    ])
                                    ->required()
                                    ->default('gula_semut')
                                    ->live(),

                                DatePicker::make('tanggal_setor')
                                    ->label('Tanggal Setor')
                                    ->default(today())
                                    ->required()
                                    ->live(),

                                TextInput::make('berat_kg')
                                    ->label('Berat Setoran (kg)')
                                    ->numeric()
                                    ->step(0.1)
                                    ->suffix('kg')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $get, $set) {
                                        $petaniId = $get('petani_id');
                                        $tanggal = $get('tanggal_setor');
                                        $jenisProduk = $get('jenis_produk');
                                        
                                        // Cek koefisien anomali
                                        if ($petaniId && $state) {
                                            $totalPohon = Petani::find($petaniId)?->lahans()->sum('jumlah_pohon') ?? 0;
                                            $koefisienMax = Setting::getValue('koefisien_max_kg_per_pohon', 0.75);
                                            
                                            if ($totalPohon > 0) {
                                                $kgPerPohon = (float)$state / $totalPohon;
                                                if ($kgPerPohon > (float)$koefisienMax) {
                                                    $set('is_anomali', true);
                                                    $set('keterangan_anomali', "⚠️ Setoran melebihi batas! " . number_format($kgPerPohon, 3) . " kg/pohon (batas: {$koefisienMax} kg/pohon)");
                                                } else {
                                                    $set('is_anomali', false);
                                                    $set('keterangan_anomali', null);
                                                }
                                            }
                                        }
                                        
                                        // Hitung total harga
                                        if ($tanggal && $jenisProduk && $state) {
                                            $harga = HargaHarian::where('tanggal', $tanggal)
                                                ->where('jenis_produk', $jenisProduk)
                                                ->first();
                                            if ($harga) {
                                                $set('total_harga', round((float)$state * $harga->harga_per_kg, 2));
                                            }
                                        }
                                    }),

                                TextInput::make('hari_akumulasi')
                                    ->label('Hari Akumulasi')
                                    ->numeric()
                                    ->default(1)
                                    ->suffix('hari')
                                    ->helperText('Berapa hari hasil ini dikumpulkan'),

                                Select::make('pengepul_id')
                                    ->label('Via Pengepul (Opsional)')
                                    ->relationship('pengepul', 'nama_koperasi')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->placeholder('— Langsung ke KUB —'),
                            ]),
                    ]),

                Section::make('Harga & Total')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_harga')
                                    ->label('Total Harga (Rp)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->helperText('Otomatis dihitung dari harga harian × berat'),

                                Select::make('batch_produksi_id')
                                    ->label('Batch Produksi (Opsional)')
                                    ->relationship('batchProduksi', 'trace_id')
                                    ->searchable()
                                    ->nullable()
                                    ->placeholder('— Belum masuk batch —'),
                            ]),
                    ]),

                Section::make('Status Anomali')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_anomali')
                                    ->label('Terdeteksi Anomali')
                                    ->helperText("Otomatis jika melebihi " . Setting::getValue('koefisien_max_kg_per_pohon', 0.75) . " kg/pohon/hari")
                                    ->disabled(),

                                Textarea::make('keterangan_anomali')
                                    ->label('Keterangan Anomali')
                                    ->rows(2)
                                    ->nullable(),
                            ]),
                    ])
                    ->collapsed(),
            ]);
    }
}
