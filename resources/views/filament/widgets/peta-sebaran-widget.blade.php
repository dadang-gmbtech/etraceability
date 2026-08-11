@once
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
@endonce

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
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">TAMPILKAN LAYER:</span>

            <label class="flex items-center gap-1.5 cursor-pointer select-none text-sm text-gray-600 dark:text-gray-300">
                <input type="checkbox" checked onchange="admToggleLayer('lahan', this.checked)"
                       class="rounded text-emerald-500 focus:ring-emerald-400">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                Lahan ({{ $lahans->count() }})
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer select-none text-sm text-gray-600 dark:text-gray-300">
                <input type="checkbox" checked onchange="admToggleLayer('pengepul', this.checked)"
                       class="rounded text-blue-500 focus:ring-blue-400">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                Pengepul ({{ $pengepul->count() }})
            </label>

            <label class="flex items-center gap-1.5 cursor-pointer select-none text-sm text-gray-600 dark:text-gray-300">
                <input type="checkbox" checked onchange="admToggleLayer('iot', this.checked)"
                       class="rounded text-orange-500 focus:ring-orange-400">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                Perangkat IoT ({{ $devices->count() }})
            </label>
        </div>

        {{-- Map — Alpine x-init menggantikan @script agar lebih andal di Filament widget --}}
        <div
            wire:ignore
            x-data="{}"
            x-init="
                (function waitL() {
                    if (!window.L || !document.getElementById('adm-map-sebaran')) {
                        return setTimeout(waitL, 100);
                    }

                    if (window._admMap) {
                        window._admMap.remove();
                        window._admMap = null;
                    }

                    const layers = {
                        lahan:    L.layerGroup(),
                        pengepul: L.layerGroup(),
                        iot:      L.layerGroup(),
                    };

                    const map = L.map('adm-map-sebaran').setView([-7.281166, 109.286804], 11);
                    window._admMap = map;
                    window.admToggleLayers = layers;

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    Object.values(layers).forEach(lg => lg.addTo(map));

                    window.admToggleLayer = function(name, show) {
                        show ? layers[name].addTo(map) : map.removeLayer(layers[name]);
                    };

                    function lahanIcon() {
                        const svg = '<svg width=\"32\" height=\"42\" viewBox=\"0 0 32 42\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M16 1C9.92 1 5 5.92 5 12c0 9 11 27 11 27S27 21 27 12c0-6.08-4.92-11-11-11z\" fill=\"#16a34a\" stroke=\"#14532d\" stroke-width=\"1\"/><circle cx=\"16\" cy=\"12\" r=\"6\" fill=\"white\"/><line x1=\"16\" y1=\"17\" x2=\"16\" y2=\"10\" stroke=\"#16a34a\" stroke-width=\"1.5\"/><ellipse cx=\"14\" cy=\"11\" rx=\"3\" ry=\"1.5\" fill=\"#16a34a\" transform=\"rotate(-20 14 11)\"/><ellipse cx=\"18\" cy=\"11\" rx=\"3\" ry=\"1.5\" fill=\"#16a34a\" transform=\"rotate(20 18 11)\"/><ellipse cx=\"16\" cy=\"9.5\" rx=\"3\" ry=\"1.5\" fill=\"#16a34a\"/></svg>';
                        return L.divIcon({ className:'', html:svg, iconSize:[32,42], iconAnchor:[16,42], popupAnchor:[0,-42] });
                    }

                    function pengepulIcon() {
                        const svg = '<svg width=\"32\" height=\"42\" viewBox=\"0 0 32 42\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M16 1C9.92 1 5 5.92 5 12c0 9 11 27 11 27S27 21 27 12c0-6.08-4.92-11-11-11z\" fill=\"#3b82f6\" stroke=\"#1e3a8a\" stroke-width=\"1\"/><circle cx=\"16\" cy=\"12\" r=\"6\" fill=\"white\"/><rect x=\"11\" y=\"13\" width=\"10\" height=\"5\" rx=\"0.5\" fill=\"#3b82f6\"/><polygon points=\"16,8 11,13 21,13\" fill=\"#3b82f6\"/></svg>';
                        return L.divIcon({ className:'', html:svg, iconSize:[32,42], iconAnchor:[16,42], popupAnchor:[0,-42] });
                    }

                    function iotIcon(active) {
                        const fill = active ? '#f97316' : '#9ca3af';
                        const ring = active ? '#ea580c' : '#6b7280';
                        const svg = '<svg width=\"30\" height=\"38\" viewBox=\"0 0 30 38\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M15 1C9.48 1 5 5.48 5 11c0 8.5 10 24 10 24s10-15.5 10-24c0-5.52-4.48-10-10-10z\" fill=\"'+fill+'\" stroke=\"'+ring+'\" stroke-width=\"1\"/><circle cx=\"15\" cy=\"11\" r=\"4.5\" fill=\"white\"/><circle cx=\"15\" cy=\"11\" r=\"1.5\" fill=\"'+fill+'\"/></svg>';
                        return L.divIcon({ className:'', html:svg, iconSize:[30,38], iconAnchor:[15,38], popupAnchor:[0,-38] });
                    }

                    const allPoints = [];

                    @foreach ($lahans as $lahan)
                        @php
                            $geom = $lahan->koordinat;
                            if (is_string($geom)) { $geom = json_decode($geom, true); }
                        @endphp
                        @if ($geom && isset($geom['type']))
                        (function() {
                            const geom = @json($geom);
                            const popup = '<b>@js($lahan->kode_lahan)</b>'
                                + '<br>Petani: @js($lahan->petani?->nama ?? "—")'
                                + '<br>Pemilik: @js($lahan->pemilik ?? "—")'
                                + '<br>{{ $lahan->kelapa_buah }} kelapa buah';

                            if (geom.type === 'FeatureCollection') {
                                geom.features.forEach(function(f) {
                                    addGeom(f.geometry, popup);
                                });
                            } else {
                                addGeom(geom, popup);
                            }

                            function addGeom(g, p) {
                                if (g.type === 'Point') {
                                    const [lng, lat] = g.coordinates;
                                    layers.lahan.addLayer(L.marker([lat,lng], {icon:lahanIcon()}).bindPopup(p));
                                    allPoints.push([lat, lng]);
                                } else if (g.type === 'Polygon' || g.type === 'MultiPolygon') {
                                    const coords = g.type === 'Polygon'
                                        ? g.coordinates[0].map(c => [c[1],c[0]])
                                        : g.coordinates.flat(1).map(c => [c[1],c[0]]);
                                    const poly = L.polygon(coords, {color:'#ea580c',fillColor:'#f97316',fillOpacity:0.35,weight:2}).bindPopup(p);
                                    layers.lahan.addLayer(poly);
                                    coords.forEach(c => allPoints.push(c));
                                }
                            }
                        })();
                        @endif
                    @endforeach

                    @foreach ($pengepul as $p)
                        @if ($p->lokasi_lat && $p->lokasi_lng)
                        (function() {
                            const m = L.marker([{{ $p->lokasi_lat }}, {{ $p->lokasi_lng }}], {icon: pengepulIcon()})
                                .bindPopup('<b>{{ addslashes($p->nama_koperasi) }}</b><br>Pengepul / Koperasi');
                            layers.pengepul.addLayer(m);
                            allPoints.push([{{ $p->lokasi_lat }}, {{ $p->lokasi_lng }}]);
                        })();
                        @endif
                    @endforeach

                    @foreach ($devices as $device)
                    (function() {
                        const lat = {{ $device->latitude }};
                        const lng = {{ $device->longitude }};
                        const active = {{ $device->status === 'active' ? 'true' : 'false' }};
                        const popup = '<b>📡 @js($device->name)</b>'
                            + '<br>Lahan: @js($device->lahan?->kode_lahan ?? "—")'
                            + '<br>Petani: @js($device->lahan?->petani?->nama ?? "—")';
                        layers.iot.addLayer(L.marker([lat,lng], {icon:iotIcon(active)}).bindPopup(popup));
                        allPoints.push([lat, lng]);
                    })();
                    @endforeach

                    if (allPoints.length > 0) {
                        try { map.fitBounds(allPoints, {padding:[40,40]}); } catch(e) {}
                    }

                    setTimeout(() => map.invalidateSize(), 500);
                })();
            "
        >
            <div id="adm-map-sebaran" style="height: 520px;" class="rounded-xl border border-gray-200 shadow-sm"></div>
        </div>
    </div>

</div>
