<?php

namespace App\Filament\Resources\HargaHarians\Widgets;

use App\Models\HargaHarian;
use Filament\Widgets\Widget;

class HargaHarianChartWidget extends Widget
{
    protected string $view = 'filament.widgets.harga-harian-chart';

    protected int|string|array $columnSpan = 'full';

    public string $filter = '30';

    public function getFilters(): array
    {
        return [
            '7'   => '7 Hari Terakhir',
            '30'  => '30 Hari Terakhir',
            '90'  => '3 Bulan Terakhir',
            '180' => '6 Bulan Terakhir',
            '365' => '1 Tahun Terakhir',
        ];
    }

    public function getChartData(): array
    {
        $days = (int) $this->filter;
        $dari = today()->subDays($days)->toDateString();

        $records  = HargaHarian::where('tanggal', '>=', $dari)->orderBy('tanggal')->get();
        $tanggals = $records->pluck('tanggal')->unique()->sort()->values();
        $labels   = $tanggals->map(fn ($t) => $t->format('d/m'))->all();

        $produkConfig = [
            'gula_semut' => ['label' => 'Gula Semut', 'color' => 'rgba(16,185,129,1)',  'fill' => 'rgba(16,185,129,0.08)'],
            'raw_sugar'  => ['label' => 'Raw Sugar',  'color' => 'rgba(59,130,246,1)',  'fill' => 'rgba(59,130,246,0.08)'],
            'nira'       => ['label' => 'Nira',        'color' => 'rgba(139,92,246,1)', 'fill' => 'rgba(139,92,246,0.08)'],
            'gula_cair'  => ['label' => 'Gula Cair',  'color' => 'rgba(249,115,22,1)', 'fill' => 'rgba(249,115,22,0.08)'],
        ];

        $datasets = [];
        foreach ($produkConfig as $jenis => $cfg) {
            $byDate = $records->where('jenis_produk', $jenis)
                ->keyBy(fn ($r) => $r->tanggal->format('Y-m-d'));

            $data = $tanggals->map(fn ($t) =>
                $byDate->has($t->format('Y-m-d'))
                    ? (float) $byDate[$t->format('Y-m-d')]->harga_per_kg
                    : null
            )->all();

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
                    'tension'              => 0.35,
                    'fill'                 => true,
                    'spanGaps'             => true,
                ];
            }
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }
}
