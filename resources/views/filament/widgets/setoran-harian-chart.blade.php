<x-filament-widgets::widget>
@php $chartData = $this->getChartData(); @endphp

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Grafik Setoran Per Hari</h3>
            <p class="text-xs text-gray-400 mt-0.5">30 hari terakhir — seluruh petani</p>
        </div>
        @if(!empty($chartData['datasets']))
        <div class="flex flex-wrap gap-3">
            @foreach($chartData['datasets'] as $ds)
            <span class="inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <span style="width:20px;height:2px;border-radius:2px;background:{{ $ds['borderColor'] }};display:inline-block"></span>
                {{ $ds['label'] }}
            </span>
            @endforeach
        </div>
        @endif
    </div>

    @if(empty($chartData['datasets']))
        <p class="text-sm text-gray-400 py-6 text-center">Belum ada data setoran dalam 30 hari terakhir.</p>
    @else
        <div style="position:relative;height:260px;">
            <canvas id="setoran-harian-chart"></canvas>
        </div>
    @endif
</div>

@if(!empty($chartData['datasets']))
@script
<script>
(function() {
    var chartData = @json($chartData);

    function init() {
        var canvas = document.getElementById('setoran-harian-chart');
        if (!canvas) return setTimeout(init, 200);
        if (canvas._chart) { canvas._chart.destroy(); }

        canvas._chart = new Chart(canvas, {
            type: 'line',
            data: { labels: chartData.labels, datasets: chartData.datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.parsed.y === null) return null;
                                return ' ' + ctx.dataset.label + ': ' + ctx.parsed.y.toFixed(2) + ' kg';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxTicksLimit: 15, maxRotation: 45, font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(156,163,175,0.12)' },
                        ticks: { callback: function(v) { return v + ' kg'; }, font: { size: 11 } }
                    }
                }
            }
        });
    }
    init();
})();
</script>
@endscript
@endif
</x-filament-widgets::widget>
