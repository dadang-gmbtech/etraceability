<?php

namespace App\Filament\Resources\SetoranGulas\Schemas;

use App\Models\BatchProduksi;
use App\Models\HargaHarian;
use App\Models\Petani;
use App\Models\Setting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
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
                        // Baris scan QR
                        Grid::make(['default' => 1, 'md' => 2])->schema([
                            TextInput::make('kode_qr_scan')
                                ->label('Scan QR / Kode Petani')
                                ->placeholder('Arahkan scanner hardware atau ketik kode petani...')
                                ->helperText('Hardware QR scanner: scan langsung, otomatis terisi. Atau klik "Buka Kamera" untuk scan via kamera.')
                                ->live(debounce: 600)
                                ->dehydrated(false)
                                ->afterStateUpdated(function ($state, $set) {
                                    if (empty(trim((string) $state))) return;
                                    $kode   = strtoupper(trim((string) $state));
                                    $petani = Petani::where('kode_petani', $kode)->first();
                                    if ($petani) {
                                        $set('petani_id', $petani->id);
                                    }
                                    $set('kode_qr_scan', null);
                                }),
                            Placeholder::make('qr_camera_ui')
                                ->label('Kamera QR')
                                ->content(fn () => new HtmlString(
                                    view('filament.forms.components.qr-petani-camera')->render()
                                )),
                        ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('petani_id')
                                    ->label('Petani')
                                    ->relationship('petani', 'nama')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "[{$record->kode_petani}] {$record->nama}")
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, $set) => null),

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
                                        $petaniId    = $get('petani_id');
                                        $tanggal     = $get('tanggal_setor');
                                        $jenisProduk = $get('jenis_produk');

                                        // Cek anomali berdasarkan threshold per jenis produk
                                        if ($petaniId && $state) {
                                            $totalPohon = Petani::find($petaniId)?->lahans()->sum('pohon_di_deres') ?? 0;

                                            $settingKey = match ($jenisProduk) {
                                                'gula_semut' => 'anomali_max_gula_semut_per_pohon',
                                                'raw_sugar'  => 'anomali_max_raw_sugar_per_pohon',
                                                'nira'       => 'anomali_max_nira_per_pohon',
                                                'gula_cair'  => 'anomali_max_gula_cair_per_pohon',
                                                default      => 'koefisien_max_kg_per_pohon',
                                            };
                                            $batasMax = (float) Setting::getValue($settingKey, 0.75);

                                            if ($totalPohon > 0) {
                                                $perPohon = (float) $state / $totalPohon;
                                                if ($perPohon > $batasMax) {
                                                    $set('is_anomali', true);
                                                    $set('keterangan_anomali',
                                                        '⚠️ Setoran melebihi batas! '
                                                        . number_format($perPohon, 3)
                                                        . ' per pohon (batas: ' . $batasMax . ' per pohon)'
                                                    );
                                                } else {
                                                    $set('is_anomali', false);
                                                    $set('keterangan_anomali', null);
                                                }
                                            }
                                        }
                                        
                                        // Hitung total harga — ambil harga terakhir yang berlaku (≤ tanggal setoran)
                                        if ($tanggal && $jenisProduk && $state) {
                                            $harga = HargaHarian::where('jenis_produk', $jenisProduk)
                                                ->where('tanggal', '<=', $tanggal)
                                                ->orderByDesc('tanggal')
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
                                    ->helperText(
                                        'Batas per jenis produk — '
                                        . 'Gula Semut: ' . Setting::getValue('anomali_max_gula_semut_per_pohon', 0.75) . ' kg | '
                                        . 'Raw Sugar: '  . Setting::getValue('anomali_max_raw_sugar_per_pohon', 0.75) . ' kg | '
                                        . 'Nira: '       . Setting::getValue('anomali_max_nira_per_pohon', 2.0)  . ' L | '
                                        . 'Gula Cair: '  . Setting::getValue('anomali_max_gula_cair_per_pohon', 1.5) . ' kg'
                                        . ' — (per pohon di deres)'
                                    )
                                    ->disabled(),

                                Textarea::make('keterangan_anomali')
                                    ->label('Keterangan Anomali')
                                    ->rows(2)
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }
}
