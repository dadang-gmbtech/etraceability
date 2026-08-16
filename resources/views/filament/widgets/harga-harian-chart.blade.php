<x-filament-widgets::widget>
@php $chartData = $this->getChartData(); $filters = $this->getFilters(); @endphp

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Grafik Perubahan Harga Produk</h3>
            <p class="text-xs text-gray-400 mt-0.5">Harga per kg masing-masing produk</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Filter dropdown --}}
            <select wire:model.live="filter"
                class="text-xs border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:border-amber-400">
                @foreach($filters as $val => $label)
                    <option value="{{ $val }}" {{ $this->filter === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            {{-- Legenda --}}
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
    </div>

    @if(empty($chartData['datasets']))
        <p class="text-sm text-gray-400 py-8 text-center">Belum ada data harga dalam periode ini.</p>
    @else
        <div style="position:relative;height:280px;">
            <canvas id="harga-harian-chart-{{ $this->filter }}"></canvas>
        </div>
    @endif
</div>

@if(!empty($chartData['datasets']))
@script
<script>
(function() {
    var chartData = @json($chartData);
    var canvasId  = 'harga-harian-chart-' + @json($this->filter);

    function init() {
        var canvas = document.getElementById(canvasId);
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
                                return ' ' + ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID');
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
                        beginAtZero: false,
                        grid: { color: 'rgba(156,163,175,0.12)' },
                        ticks: {
                            callback: function(v) { return 'Rp ' + v.toLocaleString('id-ID'); },
                            font: { size: 11 }
                        }
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
