<div class="fi-wi-peta-sebaran">

    {{-- Statistik --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border-l-4 border-emerald-500">
            <p class="text-gray-400 text-xs">Petani Aktif</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $statistik['total_petani'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border-l-4 border-teal-500">
            <p class="text-gray-400 text-xs">Total Lahan</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $statistik['total_lahan'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border-l-4 border-lime-500">
            <p class="text-gray-400 text-xs">Total Pohon Kelapa</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $statistik['total_pohon'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-gray-400 text-xs">Pengepul / Koperasi</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $statistik['total_pengepul'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <p class="text-gray-400 text-xs">Total Batch</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $statistik['total_batch'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border-l-4 border-orange-500">
            <p class="text-gray-400 text-xs">Perangkat IoT</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $statistik['total_device'] }}</p>
        </div>
    </div>

    {{-- Header peta --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-1">Peta Sebaran Lahan & Perangkat IoT</h2>
        <p class="text-xs text-gray-400 mb-3">Visualisasi spasial lahan kelapa dan perangkat IoT dalam rantai pasok gula kelapa.</p>

        {{-- Layer Toggle --}}
        <div class="flex flex-wrap items-center gap-3 mb-3">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Layer:</span>

            <label class="flex items-center gap-1.5 cursor-pointer select-none text-sm text-gray-600 dark:text-gray-300">
                <input type="checkbox" id="adm-toggle-lahan" checked onchange="admToggleLayer('lahan', this.checked)"
                       class="rounded text-emerald-500 focus:ring-emerald-400">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                Lahan ({{ $lahans->count() }})
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer select-none text-sm text-gray-600 dark:text-gray-300">
                <input type="checkbox" id="adm-toggle-pengepul" checked onchange="admToggleLayer('pengepul', this.checked)"
                       class="rounded text-blue-500 focus:ring-blue-400">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                Pengepul ({{ $pengepul->count() }})
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer select-none text-sm text-gray-600 dark:text-gray-300">
                <input type="checkbox" id="adm-toggle-iot" checked onchange="admToggleLayer('iot', this.checked)"
                       class="rounded text-orange-500 focus:ring-orange-400">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                IoT ({{ $devices->count() }})
            </label>
        </div>

        {{-- Map --}}
        <div id="adm-map-sebaran" wire:ignore style="height: 500px;" class="rounded-xl border border-gray-200 shadow-sm"></div>
    </div>

</div>

@script
<script>
(function() {
    // Tunggu sampai Leaflet tersedia
    function waitForLeaflet(cb) {
        if (window.L) { cb(); } else { setTimeout(() => waitForLeaflet(cb), 100); }
    }

    waitForLeaflet(function() {
        // Hancurkan instance lama jika ada (untuk Livewire re-render)
        if (window._admMap) {
            window._admMap.remove();
            window._admMap = null;
        }

        const layers = {
            lahan:    L.layerGroup(),
            pengepul: L.layerGroup(),
            iot:      L.layerGroup(),
        };

        const map = L.map('adm-map-sebaran').setView([-7.7956, 110.3695], 11);
        window._admMap = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        Object.values(layers).forEach(lg => lg.addTo(map));

        window.admToggleLayer = function(name, show) {
            show ? layers[name].addTo(map) : map.removeLayer(layers[name]);
        };

        function lahanIcon() {
            const svg = `<svg width="32" height="42" viewBox="0 0 32 42" xmlns="http://www.w3.org/2000/svg" style="filter:drop-shadow(0 2px 4px rgba(0,0,0,0.4))">
              <path d="M16 1C9.92 1 5 5.92 5 12c0 9 11 27 11 27S27 21 27 12c0-6.08-4.92-11-11-11z" fill="#16a34a" stroke="#14532d" stroke-width="1"/>
              <circle cx="16" cy="12" r="6" fill="white"/>
              <line x1="16" y1="17" x2="16" y2="10" stroke="#16a34a" stroke-width="1.5"/>
              <ellipse cx="14" cy="11" rx="3" ry="1.5" fill="#16a34a" transform="rotate(-20 14 11)"/>
              <ellipse cx="18" cy="11" rx="3" ry="1.5" fill="#16a34a" transform="rotate(20 18 11)"/>
              <ellipse cx="16" cy="9.5" rx="3" ry="1.5" fill="#16a34a"/>
            </svg>`;
            return L.divIcon({ className:'', html:svg, iconSize:[32,42], iconAnchor:[16,42], popupAnchor:[0,-42] });
        }

        function pengepulIcon() {
            const svg = `<svg width="32" height="42" viewBox="0 0 32 42" xmlns="http://www.w3.org/2000/svg" style="filter:drop-shadow(0 2px 4px rgba(0,0,0,0.4))">
              <path d="M16 1C9.92 1 5 5.92 5 12c0 9 11 27 11 27S27 21 27 12c0-6.08-4.92-11-11-11z" fill="#3b82f6" stroke="#1e3a8a" stroke-width="1"/>
              <circle cx="16" cy="12" r="6" fill="white"/>
              <rect x="11" y="13" width="10" height="5" rx="0.5" fill="#3b82f6"/>
              <polygon points="16,8 11,13 21,13" fill="#3b82f6"/>
            </svg>`;
            return L.divIcon({ className:'', html:svg, iconSize:[32,42], iconAnchor:[16,42], popupAnchor:[0,-42] });
        }

        function iotIcon(active) {
            const fill = active ? '#f97316' : '#9ca3af';
            const ring = active ? '#ea580c' : '#6b7280';
            const svg = `<svg width="30" height="38" viewBox="0 0 30 38" xmlns="http://www.w3.org/2000/svg" style="filter:drop-shadow(0 2px 3px rgba(0,0,0,0.35))">
              <path d="M15 1C9.48 1 5 5.48 5 11c0 8.5 10 24 10 24s10-15.5 10-24c0-5.52-4.48-10-10-10z" fill="${fill}" stroke="${ring}" stroke-width="1"/>
              <circle cx="15" cy="11" r="4.5" fill="white"/>
              <circle cx="15" cy="11" r="1.5" fill="${fill}"/>
              <path d="M12.2 9.2 a4 4 0 0 1 5.6 0" stroke="${fill}" stroke-width="1.4" fill="none" stroke-linecap="round"/>
              <path d="M10 7 a7.5 7.5 0 0 1 10 0" stroke="${fill}" stroke-width="1.4" fill="none" stroke-linecap="round"/>
            </svg>`;
            return L.divIcon({ className:'', html:svg, iconSize:[30,38], iconAnchor:[15,38], popupAnchor:[0,-38] });
        }

        const allPoints = [];

        // ── Lahan ──────────────────────────────────────────────────────────
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
                    const p = L.polygon(coords, { color: '#ea580c', fillColor: '#f97316', fillOpacity: 0.35, weight: 2 }).bindPopup(popup);
                    layers.lahan.addLayer(p);
                    coords.forEach(c => allPoints.push(c));
                }
            })();
            @endif
        @endforeach

        // ── Pengepul ───────────────────────────────────────────────────────
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

        // ── IoT Devices ────────────────────────────────────────────────────
        @foreach ($devices as $device)
            @php $lastMeasure = $device->soilMeasurements->first(); @endphp
            (function() {
                const lat    = {{ $device->latitude }};
                const lng    = {{ $device->longitude }};
                const active = {{ $device->status === 'active' ? 'true' : 'false' }};

                let popup = `<b>📡 @js($device->name)</b>`
                    + `<br><span style="color:${active ? '#16a34a' : '#6b7280'}">${active ? '● Aktif' : '○ Nonaktif'}</span>`
                    + `<br>Lahan: @js($device->lahan?->nama_lahan ?? '—')`
                    + `<br>Petani: @js($device->lahan?->petani?->nama ?? '—')`;

                @if ($lastMeasure)
                popup += '<hr style="margin:4px 0"><b style="font-size:11px">Data Terakhir:</b>';
                @if ($lastMeasure->ph_level)    popup += '<br>pH: {{ $lastMeasure->ph_level }}';       @endif
                @if ($lastMeasure->moisture)    popup += '<br>Kelembapan: {{ $lastMeasure->moisture }}%'; @endif
                @if ($lastMeasure->nitrogen)    popup += '<br>Nitrogen: {{ $lastMeasure->nitrogen }}';  @endif
                @if ($lastMeasure->temperature) popup += '<br>Suhu: {{ $lastMeasure->temperature }}°C'; @endif
                @endif

                const m = L.marker([lat, lng], { icon: iotIcon(active) }).bindPopup(popup);
                layers.iot.addLayer(m);
                allPoints.push([lat, lng]);
            })();
        @endforeach

        if (allPoints.length > 0) {
            map.fitBounds(allPoints, { padding: [40, 40] });
        }

        // Pastikan ukuran map benar setelah Filament render
        setTimeout(() => map.invalidateSize(), 300);
    });
})();
</script>
@endscript
