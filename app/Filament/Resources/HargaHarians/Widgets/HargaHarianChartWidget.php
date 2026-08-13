<?php

namespace App\Filament\Resources\HargaHarians\Widgets;

use App\Models\HargaHarian;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class HargaHarianChartWidget extends ChartWidget
{
    protected ?string $heading = 'Grafik Perubahan Harga Produk';

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7'   => '7 Hari Terakhir',
            '30'  => '30 Hari Terakhir',
            '90'  => '3 Bulan Terakhir',
            '180' => '6 Bulan Terakhir',
            '365' => '1 Tahun Terakhir',
        ];
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?? 30);
        $dari = today()->subDays($days)->toDateString();

        $records = HargaHarian::where('tanggal', '>=', $dari)
            ->orderBy('tanggal')
            ->get();

        $tanggals = $records->pluck('tanggal')->unique()->sort()->values();
        $labels   = $tanggals->map(fn ($t) => $t->format('d/m/Y'))->all();

        $produk = [
            'gula_semut' => ['label' => 'Gula Semut',  'border' => 'rgb(16,185,129)',  'bg' => 'rgba(16,185,129,0.08)'],
            'raw_sugar'  => ['label' => 'Raw Sugar',   'border' => 'rgb(59,130,246)',  'bg' => 'rgba(59,130,246,0.08)'],
            'nira'       => ['label' => 'Nira',         'border' => 'rgb(139,92,246)', 'bg' => 'rgba(139,92,246,0.08)'],
            'gula_cair'  => ['label' => 'Gula Cair',   'border' => 'rgb(249,115,22)', 'bg' => 'rgba(249,115,22,0.08)'],
        ];

        $datasets = [];
        foreach ($produk as $jenis => $cfg) {
            $byDate = $records->where('jenis_produk', $jenis)
                ->keyBy(fn ($r) => $r->tanggal->format('Y-m-d'));

            $data = $tanggals->map(fn ($t) => $byDate->has($t->format('Y-m-d'))
                ? (float) $byDate[$t->format('Y-m-d')]->harga_per_kg
                : null
            )->all();

            $datasets[] = [
                'label'           => $cfg['label'],
                'data'            => $data,
                'borderColor'     => $cfg['border'],
                'backgroundColor' => $cfg['bg'],
                'borderWidth'     => 2,
                'pointRadius'     => 3,
                'pointHoverRadius'=> 5,
                'tension'         => 0.35,
                'fill'            => true,
                'spanGaps'        => true,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels'   => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array|RawJs|null
    {
        return [
            'plugins' => [
                'legend' => [
                    'display'  => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => RawJs::make(<<<JS
                            function(ctx) {
                                return ' ' + ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID');
                            }
                        JS),
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => false,
                    'ticks'       => [
                        'callback' => RawJs::make(<<<JS
                            function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        JS),
                    ],
                    'grid' => ['color' => 'rgba(156,163,175,0.15)'],
                ],
                'x' => [
                    'grid' => ['display' => false],
                ],
            ],
            'interaction' => [
                'mode'        => 'index',
                'intersect'   => false,
            ],
        ];
    }
}
