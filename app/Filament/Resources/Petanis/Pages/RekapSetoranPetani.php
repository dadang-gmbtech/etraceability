<?php

namespace App\Filament\Resources\Petanis\Pages;

use App\Filament\Resources\Petanis\PetaniResource;
use App\Models\SetoranGula;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class RekapSetoranPetani extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = PetaniResource::class;
    protected string $view = 'filament.resources.petanis.pages.rekap-setoran-petani';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return 'Rekap Setoran — ' . $this->record->nama;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kembali')
                ->label('← Kembali ke Daftar Petani')
                ->url(PetaniResource::getUrl('index'))
                ->color('gray'),
            Action::make('edit_petani')
                ->label('Edit Petani')
                ->url(PetaniResource::getUrl('edit', ['record' => $this->record]))
                ->color('warning'),
        ];
    }

    // ── Statistik untuk stat cards ────────────────────────────────────────
    public function getStatistik(): array
    {
        $petaniId = $this->record->id;

        return [
            'total_kg'     => (float) SetoranGula::where('petani_id', $petaniId)->sum('berat_kg'),
            'total_uang'   => (float) SetoranGula::where('petani_id', $petaniId)->sum('total_harga'),
            'jumlah_setor' => SetoranGula::where('petani_id', $petaniId)->count(),
            'jumlah_anomali' => SetoranGula::where('petani_id', $petaniId)->where('is_anomali', true)->count(),
            'total_lahan'  => $this->record->lahans()->count(),
            'total_pohon'  => $this->record->lahans()->sum('pohon_di_deres'),
        ];
    }

    // ── Rekap per hari (30 hari terakhir) ────────────────────────────────
    public function getRekapHarian(): \Illuminate\Support\Collection
    {
        return SetoranGula::where('petani_id', $this->record->id)
            ->where('tanggal_setor', '>=', now()->subDays(30))
            ->selectRaw("DATE(tanggal_setor) as tanggal, COUNT(*) as jumlah, SUM(berat_kg) as total_kg, SUM(total_harga) as total_harga, COUNT(CASE WHEN is_anomali THEN 1 END) as anomali")
            ->groupByRaw("DATE(tanggal_setor)")
            ->orderByRaw("DATE(tanggal_setor) DESC")
            ->get();
    }

    // ── Data grafik (60 hari terakhir, per jenis produk) ─────────────────
    public function getChartData(): array
    {
        $rows = SetoranGula::where('petani_id', $this->record->id)
            ->where('tanggal_setor', '>=', now()->subDays(60))
            ->selectRaw("DATE(tanggal_setor) as tanggal, jenis_produk, SUM(berat_kg) as total_kg")
            ->groupByRaw("DATE(tanggal_setor), jenis_produk")
            ->orderByRaw("DATE(tanggal_setor)")
            ->get();

        // Kumpulkan semua tanggal unik
        $tanggals = $rows->pluck('tanggal')->unique()->sort()->values();
        $labels   = $tanggals->map(fn ($t) => \Carbon\Carbon::parse($t)->format('d/m'))->all();

        $produkConfig = [
            'gula_semut' => ['label' => 'Gula Semut', 'color' => 'rgba(16,185,129,1)',  'fill' => 'rgba(16,185,129,0.08)'],
            'raw_sugar'  => ['label' => 'Raw Sugar',  'color' => 'rgba(59,130,246,1)',  'fill' => 'rgba(59,130,246,0.08)'],
            'nira'       => ['label' => 'Nira',        'color' => 'rgba(139,92,246,1)', 'fill' => 'rgba(139,92,246,0.08)'],
            'gula_cair'  => ['label' => 'Gula Cair',  'color' => 'rgba(249,115,22,1)', 'fill' => 'rgba(249,115,22,0.08)'],
        ];

        $datasets = [];
        foreach ($produkConfig as $jenis => $cfg) {
            $byDate = $rows->where('jenis_produk', $jenis)->keyBy('tanggal');
            $data   = $tanggals->map(fn ($t) => $byDate->has($t) ? (float) $byDate[$t]->total_kg : null)->all();

            if (array_sum(array_filter($data)) > 0) {
                $datasets[] = [
                    'label'                => $cfg['label'],
                    'data'                 => $data,
                    'borderColor'          => $cfg['color'],
                    'backgroundColor'      => $cfg['fill'],
                    'borderWidth'          => 2,
                    'pointRadius'          => 3,
                    'pointHoverRadius'     => 5,
                    'pointBackgroundColor' => $cfg['color'],
                    'tension'              => 0.4,
                    'fill'                 => true,
                    'spanGaps'             => true,
                ];
            }
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    // ── Rekap per bulan (12 bulan terakhir) ──────────────────────────────
    public function getRekapBulanan(): \Illuminate\Support\Collection
    {
        return SetoranGula::where('petani_id', $this->record->id)
            ->selectRaw("TO_CHAR(tanggal_setor, 'YYYY-MM') as bulan, COUNT(*) as jumlah, SUM(berat_kg) as total_kg, SUM(total_harga) as total_harga, COUNT(CASE WHEN is_anomali THEN 1 END) as anomali")
            ->groupBy('bulan')
            ->orderByDesc('bulan')
            ->limit(12)
            ->get();
    }

    // ── Filament Table: detail setoran ────────────────────────────────────
    public function table(Table $table): Table
    {
        return $table
            ->query(
                SetoranGula::where('petani_id', $this->record->id)
                    ->with('batchProduksi:id,trace_id')
            )
            ->columns([
                TextColumn::make('tanggal_setor')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('jenis_produk')
                    ->label('Produk')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'gula_semut' => 'success',
                        'raw_sugar'  => 'info',
                        'nira'       => 'primary',
                        'gula_cair'  => 'warning',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'gula_semut' => '🍚 Gula Semut',
                        'raw_sugar'  => '🔵 Raw Sugar',
                        'nira'       => '💧 Nira',
                        'gula_cair'  => '🫙 Gula Cair',
                        default      => $state,
                    }),
                TextColumn::make('berat_kg')
                    ->label('Berat (kg)')
                    ->numeric(1)
                    ->suffix(' kg')
                    ->sortable(),
                TextColumn::make('total_harga')
                    ->label('Hasil Penjualan')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('hari_akumulasi')
                    ->label('Hari Akum.')
                    ->suffix(' hari')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('batchProduksi.trace_id')
                    ->label('No. Batch')
                    ->placeholder('-')
                    ->toggleable(),
                IconColumn::make('is_anomali')
                    ->label('Anomali')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle'),
                TextColumn::make('keterangan_anomali')
                    ->label('Keterangan')
                    ->limit(40)
                    ->tooltip(fn ($state) => $state)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('jenis_produk')
                    ->label('Jenis Produk')
                    ->options([
                        'gula_semut' => '🍚 Gula Semut',
                        'raw_sugar'  => '🔵 Raw Sugar',
                        'nira'       => '💧 Nira',
                        'gula_cair'  => '🫙 Gula Cair',
                    ]),
                Filter::make('periode')
                    ->form([
                        DatePicker::make('dari')->label('Dari Tanggal'),
                        DatePicker::make('sampai')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query
                            ->when($data['dari'],    fn ($q) => $q->where('tanggal_setor', '>=', $data['dari']))
                            ->when($data['sampai'],  fn ($q) => $q->where('tanggal_setor', '<=', $data['sampai']));
                    }),
                Filter::make('anomali')
                    ->label('Hanya Anomali')
                    ->query(fn (Builder $q) => $q->where('is_anomali', true))
                    ->toggle(),
            ])
            ->defaultSort('tanggal_setor', 'desc')
            ->striped()
            ->paginated([15, 30, 50]);
    }
}
