<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $record     = $getRecord();
        $currentId  = $record?->id;

        // Koordinat lahan yang sedang diedit
        $rawKoordinat = $record?->koordinat;
        if (is_array($rawKoordinat)) {
            $koordinatJs = json_encode($rawKoordinat, JSON_UNESCAPED_UNICODE);
        } elseif (is_string($rawKoordinat) && strlen($rawKoordinat) > 2) {
            $koordinatJs = $rawKoordinat;
        } else {
            $koordinatJs = 'null';
        }

        // Lahan lain yang sudah ada koordinatnya — precompute sebagai array JSON
        $lahanLainData = \App\Models\Lahan::whereNotNull('koordinat')
            ->when($currentId, fn ($q) => $q->where('id', '!=', $currentId))
            ->get(['nama_lahan', 'pemilik', 'jumlah_pohon', 'koordinat'])
            ->map(fn ($l) => [
                'nama'         => $l->nama_lahan,
                'pemilik'      => $l->pemilik ?? '',
                'jumlah_pohon' => $l->jumlah_pohon,
                'geom'         => is_array($l->koordinat) ? $l->koordinat : json_decode($l->koordinat, true),
            ])
            ->filter(fn ($l) => !empty($l['geom']['type']))
            ->values();

        // State path dinamis
        $mapStatePath = $field->getStatePath();
        $pathParts    = explode('.', $mapStatePath);
        array_pop($pathParts);
        $koordinatStatePath = implode('.', $pathParts) . '.koordinat';
    @endphp

    <div wire:ignore>

        {{-- Legenda --}}
        <div class="flex flex-wrap gap-4 text-xs text-gray-500 mb-2">
            <span class="flex items-center gap-1">
                <span class="inline-block w-4 h-3 rounded" style="background:#22c55e;opacity:0.6;border:1px solid #16a34a"></span>
                Lahan ini (sedang digambar)
            </span>
            <span class="flex items-center gap-1">
                <span class="inline-block w-4 h-3 rounded" style="background:#ef4444;opacity:0.35;border:1px solid #b91c1c"></span>
                Lahan lain — hindari irisan
            </span>
            @if ($lahanLainData->count() > 0)
                <span class="text-gray-400">{{ $lahanLainData->count() }} lahan ditampilkan sebagai referensi</span>
            @endif
        </div>

        {{-- Peta --}}
        <div
            x-data="{
                map:         null,
                drawnItems:  null,
                existingData: {{ $koordinatJs }},
                lahanLain:   {{ $lahanLainData->toJson() }},
                koordinatPath: '{{ $koordinatStatePath }}',

                initMap() {
                    const el = this.$refs.mapContainer;
                    if (!el || el._leafletMap) return;

                    this.map = L.map(el).setView([-7.281166, 109.286804], 14);
                    el._leafletMap = this.map;

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19, attribution: '© OpenStreetMap'
                    }).addTo(this.map);

                    // ── Layer lahan lain (read-only, merah) ──────────────────────────
                    const refLayer = L.layerGroup().addTo(this.map);
                    const allBounds = [];

                    this.lahanLain.forEach(lahan => {
                        const geom = lahan.geom;
                        if (!geom || !geom.type) return;

                        const popup = `<b>${lahan.nama}</b>`
                            + (lahan.pemilik ? `<br>Pemilik: ${lahan.pemilik}` : '')
                            + `<br>${lahan.jumlah_pohon} pohon`
                            + `<br><small style='color:#b91c1c'>⚠️ Lahan sudah terdaftar</small>`;

                        const styleRef = {
                            color: '#b91c1c', fillColor: '#ef4444',
                            fillOpacity: 0.25, weight: 2, dashArray: '6,4'
                        };

                        if (geom.type === 'Point') {
                            const [lng, lat] = geom.coordinates;
                            L.circleMarker([lat, lng], {
                                radius: 9, color: '#b91c1c', fillColor: '#ef4444',
                                fillOpacity: 0.55, weight: 2
                            }).bindPopup(popup).addTo(refLayer);
                            allBounds.push([lat, lng]);
                        } else if (geom.type === 'Polygon') {
                            const coords = geom.coordinates[0].map(c => [c[1], c[0]]);
                            L.polygon(coords, styleRef).bindPopup(popup).addTo(refLayer);
                            coords.forEach(c => allBounds.push(c));
                        }
                    });

                    // ── Layer lahan ini (editable, hijau) ────────────────────────────
                    this.drawnItems = new L.FeatureGroup();
                    this.map.addLayer(this.drawnItems);

                    const drawControl = new L.Control.Draw({
                        edit: { featureGroup: this.drawnItems, remove: true },
                        draw: {
                            polyline: false, circle: false,
                            rectangle: false, circlemarker: false,
                            marker: { title: 'Tandai titik lokasi' },
                            polygon: {
                                allowIntersection: false,
                                showArea: true,
                                title: 'Gambar batas area lahan',
                                shapeOptions: {
                                    color: '#16a34a', fillColor: '#22c55e', fillOpacity: 0.35
                                },
                            },
                        }
                    });
                    this.map.addControl(drawControl);

                    // Muat koordinat lahan ini (jika edit)
                    if (this.existingData) {
                        try {
                            L.geoJSON(this.existingData, {
                                style: { color: '#16a34a', fillColor: '#22c55e', fillOpacity: 0.35, weight: 2 },
                                pointToLayer: (f, latlng) => L.circleMarker(latlng, {
                                    radius: 9, color: '#16a34a', fillColor: '#22c55e',
                                    fillOpacity: 0.7, weight: 2
                                })
                            }).eachLayer(layer => {
                                this.drawnItems.addLayer(layer);
                                try {
                                    if (layer.getBounds) {
                                        const b = layer.getBounds();
                                        if (b.isValid()) { allBounds.push(b.getNorthEast()); allBounds.push(b.getSouthWest()); }
                                    } else if (layer.getLatLng) {
                                        const ll = layer.getLatLng();
                                        allBounds.push([ll.lat, ll.lng]);
                                    }
                                } catch(e) {}
                            });
                        } catch(e) { console.warn('GeoJSON load error:', e); }
                    }

                    if (allBounds.length > 0) {
                        try { this.map.fitBounds(allBounds, { padding: [50, 50] }); } catch(e) {}
                    }

                    this.map.on(L.Draw.Event.CREATED, (e) => {
                        this.drawnItems.clearLayers();
                        this.drawnItems.addLayer(e.layer);
                        this.simpanKoordinat();
                    });
                    this.map.on(L.Draw.Event.EDITED,  () => this.simpanKoordinat());
                    this.map.on(L.Draw.Event.DELETED, () => this.simpanKoordinat());
                },

                simpanKoordinat() {
                    const data = this.drawnItems.toGeoJSON();
                    const val  = data.features.length > 0
                        ? JSON.stringify(data.features[0].geometry)
                        : null;
                    this.$wire.set(this.koordinatPath, val);
                }
            }"
            x-init="$nextTick(() => initMap())"
        >
            <div
                x-ref="mapContainer"
                style="height: 500px; width: 100%; z-index: 1; border-radius: 0.5rem; border: 1px solid #e2e8f0;"
            ></div>
        </div>

        <p class="text-xs text-gray-400 mt-2">
            Klik ikon <strong>📍 marker</strong> untuk titik lokasi, atau <strong>polygon</strong> untuk area lahan.
            Gambar <strong>di luar area merah</strong> agar tidak beririsan dengan lahan yang sudah ada.
        </p>
    </div>
</x-dynamic-component>
