<div class="p-6 max-w-7xl mx-auto">

    <h1 class="text-2xl font-bold text-gray-800 mb-2">Peta Sebaran Lahan & Perangkat IoT</h1>
    <p class="text-gray-500 mb-6">Visualisasi spasial lahan kelapa dan perangkat IoT dalam rantai pasok gula kelapa.</p>

    {{-- Statistik --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-emerald-500">
            <p class="text-gray-400 text-xs">Petani Aktif</p>
            <p class="text-2xl font-bold text-gray-800">{{ $statistik['total_petani'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-teal-500">
            <p class="text-gray-400 text-xs">Total Lahan</p>
            <p class="text-2xl font-bold text-gray-800">{{ $statistik['total_lahan'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-lime-500">
            <p class="text-gray-400 text-xs">Total Pohon Kelapa</p>
            <p class="text-2xl font-bold text-gray-800">{{ $statistik['total_pohon'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-gray-400 text-xs">Pengepul / Koperasi</p>
            <p class="text-2xl font-bold text-gray-800">{{ $statistik['total_pengepul'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <p class="text-gray-400 text-xs">Total Batch</p>
            <p class="text-2xl font-bold text-gray-800">{{ $statistik['total_batch'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-orange-500">
            <p class="text-gray-400 text-xs">Perangkat IoT</p>
            <p class="text-2xl font-bold text-gray-800">{{ $statistik['total_device'] }}</p>
        </div>
    </div>

    {{-- Layer Toggle & Legenda --}}
    <div class="flex flex-wrap items-center gap-3 mb-3">
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tampilkan layer:</span>

        <label class="flex items-center gap-1.5 cursor-pointer select-none text-sm">
            <input type="checkbox" id="toggle-lahan" checked onchange="toggleLayer('lahan', this.checked)"
                   class="rounded text-emerald-500 focus:ring-emerald-400">
            <svg width="14" height="18" viewBox="0 0 32 42" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 1C9.92 1 5 5.92 5 12c0 9 11 27 11 27S27 21 27 12c0-6.08-4.92-11-11-11z" fill="#16a34a" stroke="#14532d" stroke-width="1"/>
                <circle cx="16" cy="12" r="6" fill="white"/>
                <line x1="16" y1="17" x2="16" y2="10" stroke="#16a34a" stroke-width="1.5"/>
                <ellipse cx="14" cy="11" rx="3" ry="1.5" fill="#16a34a" transform="rotate(-20 14 11)"/>
                <ellipse cx="18" cy="11" rx="3" ry="1.5" fill="#16a34a" transform="rotate(20 18 11)"/>
                <ellipse cx="16" cy="9.5" rx="3" ry="1.5" fill="#16a34a"/>
            </svg>
            Lahan ({{ $lahans->count() }})
        </label>

        <label class="flex items-center gap-1.5 cursor-pointer select-none text-sm">
            <input type="checkbox" id="toggle-pengepul" checked onchange="toggleLayer('pengepul', this.checked)"
                   class="rounded text-blue-500 focus:ring-blue-400">
            <svg width="14" height="18" viewBox="0 0 32 42" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 1C9.92 1 5 5.92 5 12c0 9 11 27 11 27S27 21 27 12c0-6.08-4.92-11-11-11z" fill="#3b82f6" stroke="#1e3a8a" stroke-width="1"/>
                <circle cx="16" cy="12" r="6" fill="white"/>
                <rect x="11" y="13" width="10" height="5" rx="0.5" fill="#3b82f6"/>
                <polygon points="16,8 11,13 21,13" fill="#3b82f6"/>
            </svg>
            Pengepul ({{ $pengepul->count() }})
        </label>

        <label class="flex items-center gap-1.5 cursor-pointer select-none text-sm">
            <input type="checkbox" id="toggle-iot" checked onchange="toggleLayer('iot', this.checked)"
                   class="rounded text-orange-500 focus:ring-orange-400">
            <svg width="14" height="18" viewBox="0 0 30 38" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 1C9.48 1 5 5.48 5 11c0 8.5 10 24 10 24s10-15.5 10-24c0-5.52-4.48-10-10-10z" fill="#f97316" stroke="#ea580c" stroke-width="1"/>
                <circle cx="15" cy="11" r="4.5" fill="white"/>
                <circle cx="15" cy="11" r="1.5" fill="#f97316"/>
                <path d="M12.2 9.2 a4 4 0 0 1 5.6 0" stroke="#f97316" stroke-width="1.4" fill="none" stroke-linecap="round"/>
                <path d="M10 7 a7.5 7.5 0 0 1 10 0" stroke="#f97316" stroke-width="1.4" fill="none" stroke-linecap="round"/>
            </svg>
            Perangkat IoT ({{ $devices->count() }})
        </label>
    </div>

    {{-- Peta --}}
    <div id="map-sebaran" wire:ignore style="height: 600px;" class="rounded-xl border shadow-sm"></div>

    {{-- Daftar Device --}}
    @if ($devices->isNotEmpty())
    <div class="mt-6">
        <h2 class="text-base font-semibold text-gray-700 mb-3">📡 Daftar Perangkat IoT</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($devices as $device)
            <div class="bg-white border rounded-xl p-3 shadow-sm flex items-start gap-3">
                <div class="mt-0.5">
                    <span class="inline-block w-3 h-3 rounded-full {{ $device->status === 'active' ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm truncate">{{ $device->name }}</p>
                    <p class="text-xs text-gray-400">
                        Lahan: {{ $device->lahan?->nama_lahan ?? '—' }}
                        @if ($device->lahan?->petani)
                            · Petani: {{ $device->lahan->petani->nama }}
                        @endif
                    </p>
                    <p class="text-xs text-gray-400 font-mono">
                        {{ number_format($device->latitude, 6) }}, {{ number_format($device->longitude, 6) }}
                    </p>
                    @php $lastMeasure = $device->soilMeasurements->first(); @endphp
                    @if ($lastMeasure)
                    <div class="mt-1 flex flex-wrap gap-1">
                        @if ($lastMeasure->ph_level)
                            <span class="text-xs bg-teal-50 text-teal-700 px-1.5 py-0.5 rounded">pH {{ $lastMeasure->ph_level }}</span>
                        @endif
                        @if ($lastMeasure->moisture)
                            <span class="text-xs bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded">💧 {{ $lastMeasure->moisture }}%</span>
                        @endif
                        @if ($lastMeasure->temperature)
                            <span class="text-xs bg-orange-50 text-orange-700 px-1.5 py-0.5 rounded">🌡️ {{ $lastMeasure->temperature }}°C</span>
                        @endif
                    </div>
                    @endif
                </div>
                <span class="text-xs px-2 py-0.5 rounded font-semibold
                    {{ $device->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $device->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

@script
<script>
    const layers = { lahan: L.layerGroup(), pengepul: L.layerGroup(), iot: L.layerGroup() };

    const map = L.map('map-sebaran').setView([-7.7956, 110.3695], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    Object.values(layers).forEach(lg => lg.addTo(map));

    window.toggleLayer = function(name, show) {
        show ? layers[name].addTo(map) : map.removeLayer(layers[name]);
    };

    function lahanIcon() {
        const svg = `
        <svg width="32" height="42" viewBox="0 0 32 42" xmlns="http://www.w3.org/2000/svg"
             style="filter:drop-shadow(0 2px 4px rgba(0,0,0,0.4))">
          <path d="M16 1C9.92 1 5 5.92 5 12c0 9 11 27 11 27S27 21 27 12c0-6.08-4.92-11-11-11z"
                fill="#16a34a" stroke="#14532d" stroke-width="1"/>
          <circle cx="16" cy="12" r="6" fill="white"/>
          <!-- Pohon kelapa sederhana -->
          <line x1="16" y1="17" x2="16" y2="10" stroke="#16a34a" stroke-width="1.5"/>
          <ellipse cx="14" cy="11" rx="3" ry="1.5" fill="#16a34a" transform="rotate(-20 14 11)"/>
          <ellipse cx="18" cy="11" rx="3" ry="1.5" fill="#16a34a" transform="rotate(20 18 11)"/>
          <ellipse cx="16" cy="9.5" rx="3" ry="1.5" fill="#16a34a"/>
        </svg>`;
        return L.divIcon({ className:'', html:svg, iconSize:[32,42], iconAnchor:[16,42], popupAnchor:[0,-42] });
    }

    function pengepulIcon() {
        const svg = `
        <svg width="32" height="42" viewBox="0 0 32 42" xmlns="http://www.w3.org/2000/svg"
             style="filter:drop-shadow(0 2px 4px rgba(0,0,0,0.4))">
          <path d="M16 1C9.92 1 5 5.92 5 12c0 9 11 27 11 27S27 21 27 12c0-6.08-4.92-11-11-11z"
                fill="#3b82f6" stroke="#1e3a8a" stroke-width="1"/>
          <circle cx="16" cy="12" r="6" fill="white"/>
          <!-- Ikon gudang/koperasi -->
          <rect x="11" y="13" width="10" height="5" rx="0.5" fill="#3b82f6"/>
          <polygon points="16,8 11,13 21,13" fill="#3b82f6"/>
        </svg>`;
        return L.divIcon({ className:'', html:svg, iconSize:[32,42], iconAnchor:[16,42], popupAnchor:[0,-42] });
    }

    function iotIcon(active) {
        const fill  = active ? '#f97316' : '#9ca3af';
        const ring  = active ? '#ea580c' : '#6b7280';
        const svg = `
        <svg width="30" height="38" viewBox="0 0 30 38" xmlns="http://www.w3.org/2000/svg" style="filter:drop-shadow(0 2px 3px rgba(0,0,0,0.35))">
          <!-- Pin body -->
          <path d="M15 1C9.48 1 5 5.48 5 11c0 8.5 10 24 10 24s10-15.5 10-24c0-5.52-4.48-10-10-10z"
                fill="${fill}" stroke="${ring}" stroke-width="1"/>
          <!-- White circle center -->
          <circle cx="15" cy="11" r="4.5" fill="white"/>
          <!-- Signal dot -->
          <circle cx="15" cy="11" r="1.5" fill="${fill}"/>
          <!-- Signal arc 1 (small) -->
          <path d="M12.2 9.2 a4 4 0 0 1 5.6 0"
                stroke="${fill}" stroke-width="1.4" fill="none" stroke-linecap="round"/>
          <!-- Signal arc 2 (large) -->
          <path d="M10 7 a7.5 7.5 0 0 1 10 0"
                stroke="${fill}" stroke-width="1.4" fill="none" stroke-linecap="round"/>
        </svg>`;
        return L.divIcon({
            className: '',
            html: svg,
            iconSize: [30, 38],
            iconAnchor: [15, 38],
            popupAnchor: [0, -38],
        });
    }

    const allPoints = [];

    // ── Layer Lahan ──────────────────────────────────────────────────────────
    @foreach ($lahans as $lahan)
        @php
            $geom = $lahan->koordinat;
            if (is_string($geom)) { $geom = json_decode($geom, true); }
        @endphp
        @if ($geom && isset($geom['type']))
        (function() {
            const geom = @json($geom);
            const popup = `<b>@js($lahan->nama_lahan)</b>`
                + `<br>Petani: @js($lahan->petani?->nama ?? '—')`
                + `<br>Pemilik: @js($lahan->pemilik ?? '—')`
                + `<br>{{ $lahan->jumlah_pohon }} pohon kelapa`;

            if (geom.type === 'Point') {
                const [lng, lat] = geom.coordinates;
                const m = L.marker([lat, lng], { icon: lahanIcon() }).bindPopup(popup);
                layers.lahan.addLayer(m);
                allPoints.push([lat, lng]);
            } else if (geom.type === 'Polygon') {
                const coords = geom.coordinates[0].map(c => [c[1], c[0]]);
                const p = L.polygon(coords, { color: '#10b981', fillColor: '#10b981', fillOpacity: 0.25, weight: 2 }).bindPopup(popup);
                layers.lahan.addLayer(p);
                coords.forEach(c => allPoints.push(c));
            }
        })();
        @endif
    @endforeach

    // ── Layer Pengepul ───────────────────────────────────────────────────────
    @foreach ($pengepul as $p)
        @if ($p->lokasi_lat && $p->lokasi_lng)
        (function() {
            const m = L.marker([{{ $p->lokasi_lat }}, {{ $p->lokasi_lng }}], { icon: pengepulIcon() })
                .bindPopup('<b>{{ addslashes($p->nama_koperasi) }}</b><br>Pengepul / Koperasi');
            layers.pengepul.addLayer(m);
            allPoints.push([{{ $p->lokasi_lat }}, {{ $p->lokasi_lng }}]);
        })();
        @endif
    @endforeach

    // ── Layer IoT Devices ────────────────────────────────────────────────────
    @foreach ($devices as $device)
        @php
            $lastMeasure = $device->soilMeasurements->first();
        @endphp
        (function() {
            const lat = {{ $device->latitude }};
            const lng = {{ $device->longitude }};
            const active = {{ $device->status === 'active' ? 'true' : 'false' }};

            let popup = `<b>📡 @js($device->name)</b>`
                + `<br><span style="font-size:11px;color:${active ? '#16a34a' : '#6b7280'}">${active ? '● Aktif' : '○ Nonaktif'}</span>`
                + `<br>Lahan: @js($device->lahan?->nama_lahan ?? '—')`
                + `<br>Petani: @js($device->lahan?->petani?->nama ?? '—')`
                + `<br><small style="color:#6b7280">${lat.toFixed(6)}, ${lng.toFixed(6)}</small>`;

            @if ($lastMeasure)
            popup += `<hr style="margin:4px 0"><b style="font-size:11px">Data Terakhir:</b>`;
            @if ($lastMeasure->ph_level) popup += `<br>pH: {{ $lastMeasure->ph_level }}`; @endif
            @if ($lastMeasure->moisture) popup += `<br>Kelembapan: {{ $lastMeasure->moisture }}%`; @endif
            @if ($lastMeasure->nitrogen) popup += `<br>Nitrogen: {{ $lastMeasure->nitrogen }}`; @endif
            @if ($lastMeasure->temperature) popup += `<br>Suhu: {{ $lastMeasure->temperature }}°C`; @endif
            @endif

            const m = L.marker([lat, lng], { icon: iotIcon(active) }).bindPopup(popup);
            layers.iot.addLayer(m);
            allPoints.push([lat, lng]);
        })();
    @endforeach

    if (allPoints.length > 0) {
        map.fitBounds(allPoints, { padding: [40, 40] });
    }
</script>
@endscript
