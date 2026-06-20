<div class="p-6 max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Traceability Batch Produksi</h1>
            <p class="text-gray-500 mt-1">Asal usul produk dari lahan hingga pengumpulan</p>
        </div>
        <a href="{{ url()->previous() }}" class="text-sm text-gray-500 hover:text-gray-700">← Kembali</a>
    </div>

    {{-- Info Batch --}}
    <div class="bg-white rounded-2xl shadow-sm border p-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-400">Trace ID</p>
                <p class="font-mono font-bold text-blue-700 text-lg">{{ $batch->trace_id }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Tanggal</p>
                <p class="font-semibold">{{ $batch->tanggal_pengumpulan?->format('d/m/Y') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Status</p>
                <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold
                    {{ $batch->status_batch === 'selesai' ? 'bg-green-100 text-green-700' : ($batch->status_batch === 'diproses' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ ucfirst($batch->status_batch) }}
                </span>
            </div>
            <div>
                <p class="text-xs text-gray-400">Berat Total</p>
                <p class="font-bold text-gray-800">{{ number_format($batch->berat_total_kg, 2) }} kg</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Pengepul</p>
                <p class="font-semibold">{{ $batch->pengepul?->nama_koperasi ?? 'Langsung ke KUB' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Organik</p>
                <p class="font-semibold">{{ $batch->is_organik ? 'Ya ✓' : 'Tidak' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Jumlah Petani</p>
                <p class="font-bold text-green-700 text-lg">{{ $petaniList->count() }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Total Pohon Terlibat</p>
                <p class="font-bold text-green-700 text-lg">{{ $allLahans->sum('jumlah_pohon') }}</p>
            </div>
        </div>
    </div>

    {{-- Petani & Lahan --}}
    <div>
        <h2 class="text-lg font-semibold text-gray-700 mb-3">Petani yang Menyetorkan Produk</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($petaniList as $petaniId => $setorans)
                @php
                    $petani = $setorans->first()?->petani;
                    if (!$petani) continue;
                    $totalBerat = $setorans->sum('berat_kg');
                @endphp
                <div class="bg-white border rounded-2xl p-4 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="inline-block bg-blue-100 text-blue-700 font-mono text-xs px-2 py-0.5 rounded font-semibold">
                                {{ $petani->kode_petani }}
                            </span>
                            <h3 class="font-bold text-gray-800 mt-1">{{ $petani->nama }}</h3>
                            <p class="text-xs text-gray-400">{{ $petani->desa }}, {{ $petani->kecamatan }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400">Total Setoran</p>
                            <p class="text-xl font-bold text-green-600">{{ number_format($totalBerat, 2) }} kg</p>
                        </div>
                    </div>

                    <div class="border-t pt-2 mt-2">
                        <p class="text-xs font-semibold text-gray-500 mb-1">Lahan yang dikelola:</p>
                        @forelse ($petani->lahans as $lahan)
                            <div class="flex items-center gap-2 text-sm py-0.5">
                                <span>{{ $lahan->jenis_geometri === 'titik' ? '📍' : '📐' }}</span>
                                <span class="font-medium">{{ $lahan->nama_lahan }}</span>
                                <span class="text-gray-400 text-xs">{{ $lahan->jumlah_pohon }} pohon</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic">Belum ada lahan terdaftar</p>
                        @endforelse
                        <div class="mt-1 text-xs font-semibold text-gray-600">
                            Total: {{ $petani->lahans->sum('jumlah_pohon') }} pohon kelapa
                        </div>
                    </div>

                    <div class="border-t pt-2 mt-2">
                        <p class="text-xs font-semibold text-gray-500 mb-1">Detail setoran:</p>
                        @foreach ($setorans as $s)
                            <div class="flex justify-between text-xs py-0.5">
                                <span>{{ $s->tanggal_setor?->format('d/m/Y') }} — {{ str_replace('_', ' ', ucfirst($s->jenis_produk)) }}</span>
                                <span class="font-semibold {{ $s->is_anomali ? 'text-red-600' : '' }}">
                                    {{ number_format($s->berat_kg, 2) }} kg
                                    @if ($s->is_anomali) ⚠️ @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Map --}}
    @php $lahansWithKoordinat = $allLahans->filter(fn($l) => !empty($l->koordinat)); @endphp
    <div>
        <h2 class="text-lg font-semibold text-gray-700 mb-3">Peta Lahan Sumber Produk</h2>
        @if ($lahansWithKoordinat->isNotEmpty())
            <div id="batch-trace-map" style="height: 450px;" class="rounded-2xl border shadow-sm" wire:ignore></div>
        @else
            <div class="bg-gray-50 border rounded-2xl p-8 text-center text-gray-400">
                Belum ada data koordinat lahan. Tambahkan koordinat melalui menu Lahan.
            </div>
        @endif
    </div>

    {{-- Ringkasan --}}
    <div class="bg-green-50 border border-green-200 rounded-2xl p-5">
        <h2 class="font-semibold text-green-800 mb-3">Ringkasan Traceability</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div>
                <p class="text-3xl font-bold text-green-700">{{ $petaniList->count() }}</p>
                <p class="text-xs text-gray-500 mt-1">Petani Penyetor</p>
            </div>
            <div>
                <p class="text-3xl font-bold text-green-700">{{ $allLahans->count() }}</p>
                <p class="text-xs text-gray-500 mt-1">Lahan Terlibat</p>
            </div>
            <div>
                <p class="text-3xl font-bold text-green-700">{{ $allLahans->sum('jumlah_pohon') }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Pohon</p>
            </div>
            <div>
                <p class="text-3xl font-bold text-green-700">{{ number_format($batch->berat_total_kg, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">kg Total Produk</p>
            </div>
        </div>
    </div>

</div>

@if ($lahansWithKoordinat->isNotEmpty())
@script
<script>
    (function() {
        const el = document.getElementById('batch-trace-map');
        if (!el || el._leafletId) return;
        el._leafletId = true;

        const map = L.map(el).setView([-7.7956, 110.3695], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const bounds = [];

        @foreach ($lahansWithKoordinat as $lahan)
            @php
                $geom = is_array($lahan->koordinat)
                    ? $lahan->koordinat
                    : json_decode($lahan->koordinat, true);
            @endphp
            @if ($geom && isset($geom['type']))
            (function() {
                const geom = @json($geom);
                const popup = '<b>{{ addslashes($lahan->nama_lahan) }}</b>' +
                    '<br>Petani: {{ addslashes($lahan->petani?->nama ?? "") }}' +
                    '<br>{{ $lahan->jumlah_pohon }} pohon kelapa';

                if (geom.type === 'Point') {
                    const lat = geom.coordinates[1], lng = geom.coordinates[0];
                    L.marker([lat, lng]).addTo(map).bindPopup(popup);
                    bounds.push([lat, lng]);
                } else if (geom.type === 'Polygon') {
                    const coords = geom.coordinates[0].map(c => [c[1], c[0]]);
                    L.polygon(coords, { color: '#10b981', fillOpacity: 0.3, weight: 2 })
                        .addTo(map).bindPopup(popup);
                    coords.forEach(c => bounds.push(c));
                }
            })();
            @endif
        @endforeach

        if (bounds.length > 0) map.fitBounds(bounds, { padding: [50, 50] });
    })();
</script>
@endscript
@endif
