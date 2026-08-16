<?php

namespace App\Filament\Widgets;

use App\Models\SetoranGula;
use Filament\Widgets\Widget;

class SetoranHarianChartWidget extends Widget
{
    protected string $view      = 'filament.widgets.setoran-harian-chart';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 1;

    public function getChartData(): array
    {
        $rows = SetoranGula::query()
            ->where('tanggal_setor', '>=', now()->subDays(30))
            ->selectRaw("DATE(tanggal_setor) as tgl, jenis_produk, SUM(berat_kg) as total_kg")
            ->groupByRaw("DATE(tanggal_setor), jenis_produk")
            ->orderByRaw("DATE(tanggal_setor)")
            ->get();

        $tanggals = $rows->pluck('tgl')->unique()->sort()->values();
        $labels   = $tanggals->map(fn ($t) => \Carbon\Carbon::parse($t)->format('d/m'))->all();

        $produkConfig = [
            'gula_semut' => ['label' => 'Gula Semut', 'color' => 'rgba(16,185,129,1)',  'fill' => 'rgba(16,185,129,0.08)'],
            'raw_sugar'  => ['label' => 'Raw Sugar',  'color' => 'rgba(59,130,246,1)',  'fill' => 'rgba(59,130,246,0.08)'],
            'nira'       => ['label' => 'Nira',        'color' => 'rgba(139,92,246,1)', 'fill' => 'rgba(139,92,246,0.08)'],
            'gula_cair'  => ['label' => 'Gula Cair',  'color' => 'rgba(249,115,22,1)', 'fill' => 'rgba(249,115,22,0.08)'],
        ];

        $datasets = [];
        foreach ($produkConfig as $jenis => $cfg) {
            $byDate = $rows->where('jenis_produk', $jenis)->keyBy('tgl');
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
}
