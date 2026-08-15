<x-filament-panels::page>
@php
    $statistik    = $this->getStatistik();
    $rekapHarian  = $this->getRekapHarian();
    $rekapBulanan = $this->getRekapBulanan();
    $chartData    = $this->getChartData();
    $petani       = $this->record;
@endphp

<style>
.rekap-stat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem}
@media(max-width:640px){.rekap-stat-grid{grid-template-columns:repeat(2,1fr)}}
.rekap-stat{background:#fff;border-radius:.75rem;padding:1rem 1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.08);border-left:4px solid #ccc}
.dark .rekap-stat{background:#1f2937}
.rekap-stat .lbl{font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin:0 0 .25rem}
.rekap-stat .val{font-size:1.4rem;font-weight:700;color:#111827;margin:0}
.dark .rekap-stat .val{color:#f9fafb}
.rekap-stat .sub{font-size:.75rem;color:#6b7280;margin:.1rem 0 0}
.rekap-card{background:#fff;border-radius:.75rem;box-shadow:0 1px 3px rgba(0,0,0,.08);border:1px solid #f3f4f6;overflow:hidden;margin-bottom:1.5rem}
.dark .rekap-card{background:#1f2937;border-color:#374151}
.rekap-card-head{padding:.75rem 1.25rem;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between}
.dark .rekap-card-head{border-color:#374151}
.rekap-card-head h3{font-size:.875rem;font-weight:600;color:#374151;margin:0}
.dark .rekap-card-head h3{color:#e5e7eb}
.rekap-card-head .badge{font-size:.7rem;padding:.2rem .6rem;border-radius:9999px;background:#f3f4f6;color:#6b7280}
.dark .rekap-card-head .badge{background:#374151;color:#9ca3af}
table.rekap-tbl{width:100%;font-size:.8125rem;border-collapse:collapse}
table.rekap-tbl thead tr{background:#f9fafb}
.dark table.rekap-tbl thead tr{background:rgba(255,255,255,.04)}
table.rekap-tbl th{padding:.5rem 1rem;text-align:left;text-transform:uppercase;font-size:.68rem;letter-spacing:.05em;color:#9ca3af;font-weight:600;white-space:nowrap}
table.rekap-tbl td{padding:.5rem 1rem;color:#374151;border-top:1px solid #f3f4f6;white-space:nowrap}
.dark table.rekap-tbl td{color:#d1d5db;border-color:#374151}
table.rekap-tbl tr:hover td{background:#f9fafb}
.dark table.rekap-tbl tr:hover td{background:rgba(255,255,255,.03)}
.anomali-badge{display:inline-flex;align-items:center;gap:.25rem;font-size:.7rem;font-weight:600;padding:.15rem .5rem;border-radius:9999px}
</style>

{{-- ═══ INFO PETANI ═══ --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 px-6 py-4 mb-6 flex flex-wrap gap-6 items-center">
    <div>
        <p class="text-xs text-gray-400 uppercase tracking-wide">Kode Petani</p>
        <p class="text-lg font-bold text-amber-600">{{ $petani->kode_petani }}</p>
    </div>
    <div>
        <p class="text-xs text-gray-400 uppercase tracking-wide">Nama</p>
        <p class="text-base font-semibold text-gray-800 dark:text-gray-100">{{ $petani->nama }}</p>
    </div>
    @if($petani->no_hp)
    <div>
        <p class="text-xs text-gray-400 uppercase tracking-wide">No. HP</p>
        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $petani->no_hp }}</p>
    </div>
    @endif
    @if($petani->desa)
    <div>
        <p class="text-xs text-gray-400 uppercase tracking-wide">Desa / Kecamatan</p>
        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $petani->desa }}{{ $petani->kecamatan ? ', '.$petani->kecamatan : '' }}</p>
    </div>
    @endif
    <div class="ml-auto">
        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $petani->aktif ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
            {{ $petani->aktif ? 'Aktif' : 'Tidak Aktif' }}
        </span>
    </div>
</div>

{{-- ═══ STAT CARDS ═══ --}}
<div class="rekap-stat-grid">
    <div class="rekap-stat" style="border-color:#f59e0b">
        <p class="lbl">Total Setoran</p>
        <p class="val">{{ number_format($statistik['total_kg'], 1) }}</p>
        <p class="sub">kilogram</p>
    </div>
    <div class="rekap-stat" style="border-color:#10b981">
        <p class="lbl">Hasil Penjualan</p>
        <p class="val" style="font-size:1.05rem">Rp {{ number_format($statistik['total_uang'], 0, ',', '.') }}</p>
        <p class="sub">total diterima</p>
    </div>
    <div class="rekap-stat" style="border-color:#3b82f6">
        <p class="lbl">Jumlah Setoran</p>
        <p class="val">{{ number_format($statistik['jumlah_setor']) }}</p>
        <p class="sub">transaksi</p>
    </div>
    <div class="rekap-stat" style="border-color:#8b5cf6">
        <p class="lbl">Total Lahan</p>
        <p class="val">{{ $statistik['total_lahan'] }}</p>
        <p class="sub">{{ number_format($statistik['total_pohon']) }} pohon di deres</p>
    </div>
    <div class="rekap-stat" style="border-color:#ef4444">
        <p class="lbl">Anomali Terdeteksi</p>
        <p class="val {{ $statistik['jumlah_anomali'] > 0 ? 'text-red-600' : '' }}">{{ $statistik['jumlah_anomali'] }}</p>
        <p class="sub">dari {{ $statistik['jumlah_setor'] }} setoran</p>
    </div>
    <div class="rekap-stat" style="border-color:#14b8a6">
        <p class="lbl">Rata-rata / Setoran</p>
        <p class="val">{{ $statistik['jumlah_setor'] > 0 ? number_format($statistik['total_kg'] / $statistik['jumlah_setor'], 1) : '0' }}</p>
        <p class="sub">kg per setoran</p>
    </div>
</div>

{{-- ═══ REKAP HARIAN ═══ --}}
<div class="rekap-card">
    <div class="rekap-card-head">
        <h3>Rekap Setoran Per Hari <span class="badge">30 Hari Terakhir</span></h3>
        <span class="text-xs text-gray-400">{{ $rekapHarian->count() }} hari aktif</span>
    </div>
    @if($rekapHarian->isEmpty())
        <p class="text-sm text-gray-400 px-5 py-4">Belum ada setoran dalam 30 hari terakhir.</p>
    @else
    <div class="overflow-x-auto" style="max-height:340px;overflow-y:auto">
        <table class="rekap-tbl">
            <thead style="position:sticky;top:0;z-index:1">
                <tr>
                    <th>Tanggal</th>
                    <th class="text-right">Jumlah Setor</th>
                    <th class="text-right">Total (kg)</th>
                    <th class="text-right">Hasil Penjualan</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekapHarian as $r)
                <tr>
                    <td class="font-medium text-gray-800 dark:text-gray-200">
                        {{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('l, d F Y') }}
                    </td>
                    <td class="text-right text-gray-600 dark:text-gray-300">{{ $r->jumlah }}×</td>
                    <td class="text-right font-semibold text-gray-800 dark:text-gray-200">{{ number_format($r->total_kg, 2) }} kg</td>
                    <td class="text-right font-semibold text-green-600 dark:text-green-400">
                        Rp {{ number_format($r->total_harga, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if($r->anomali > 0)
                            <span class="anomali-badge bg-red-100 text-red-700">⚠ {{ $r->anomali }} anomali</span>
                        @else
                            <span class="anomali-badge bg-green-100 text-green-700">✓ Normal</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot style="position:sticky;bottom:0;z-index:1;background:#f9fafb;">
                <tr>
                    <td class="font-semibold text-gray-600 dark:text-gray-300">Total</td>
                    <td class="text-right font-semibold">{{ $rekapHarian->sum('jumlah') }}×</td>
                    <td class="text-right font-semibold">{{ number_format($rekapHarian->sum('total_kg'), 2) }} kg</td>
                    <td class="text-right font-semibold text-green-600">Rp {{ number_format($rekapHarian->sum('total_harga'), 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</div>

{{-- ═══ GRAFIK SETORAN ═══ --}}
<div class="rekap-card">
    <div class="rekap-card-head">
        <h3>Grafik Setoran Per Hari <span class="badge">60 Hari Terakhir</span></h3>
        <div style="display:flex;gap:.5rem;align-items:center;font-size:.7rem;color:#9ca3af;">
            @foreach($chartData['datasets'] as $ds)
                <span style="display:inline-flex;align-items:center;gap:.25rem;">
                    <span style="width:16px;height:2px;border-radius:2px;background:{{ $ds['borderColor'] }};display:inline-block"></span>
                    {{ $ds['label'] }}
                </span>
            @endforeach
        </div>
    </div>
    @if(empty($chartData['datasets']))
        <p class="text-sm text-gray-400 px-5 py-4">Belum ada data setoran dalam 60 hari terakhir.</p>
    @else
    <div style="padding:1rem 1.25rem;">
        <canvas id="rekap-chart-petani" style="width:100%;max-height:320px;"></canvas>
    </div>
    @endif
</div>

{{-- ═══ REKAP BULANAN ═══ --}}
@if($rekapBulanan->isNotEmpty())
<div class="rekap-card">
    <div class="rekap-card-head">
        <h3>Rekap Per Bulan <span class="badge">12 Bulan Terakhir</span></h3>
    </div>
    <div class="overflow-x-auto">
        <table class="rekap-tbl">
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th class="text-right">Jumlah Setor</th>
                    <th class="text-right">Total (kg)</th>
                    <th class="text-right">Hasil Penjualan</th>
                    <th class="text-center">Anomali</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekapBulanan as $r)
                <tr>
                    <td class="font-medium text-gray-800 dark:text-gray-200">
                        {{ \Carbon\Carbon::parse($r->bulan . '-01')->translatedFormat('F Y') }}
                    </td>
                    <td class="text-right text-gray-600 dark:text-gray-300">{{ $r->jumlah }}×</td>
                    <td class="text-right font-semibold text-gray-800 dark:text-gray-200">{{ number_format($r->total_kg, 1) }} kg</td>
                    <td class="text-right font-semibold text-green-600 dark:text-green-400">
                        Rp {{ number_format($r->total_harga, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if($r->anomali > 0)
                            <span class="anomali-badge bg-red-100 text-red-700">⚠ {{ $r->anomali }}</span>
                        @else
                            <span style="color:#d1d5db">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ═══ DETAIL SETORAN TABLE ═══ --}}
<div class="space-y-2">
    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 px-1">Detail Seluruh Setoran</h3>
    {{ $this->table }}
</div>

{{-- ═══ CHART.JS INIT ═══ --}}
@if(!empty($chartData['datasets']))
@php $chartJson = json_encode($chartData) @endphp
@script
<script>
(function() {
    var chartData = @json($chartData);

    function initRekapChart() {
        var canvas = document.getElementById('rekap-chart-petani');
        if (!canvas) return setTimeout(initRekapChart, 200);
        if (canvas._chart) { canvas._chart.destroy(); }

        canvas._chart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: true, position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', padding: 16 } },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.parsed.y === null) return null;
                                return ' ' + ctx.dataset.label + ': ' + ctx.parsed.y.toFixed(2) + ' kg';
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxTicksLimit: 15, maxRotation: 45 },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(156,163,175,0.15)' },
                        ticks: { callback: function(v) { return v + ' kg'; } },
                    },
                },
            },
        });
    }

    initRekapChart();
})();
</script>
@endscript
@endif

</x-filament-panels::page>
