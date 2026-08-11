<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $record    = $getRecord();
        $currentId = $record?->id;

        // Koordinat lahan yang sedang diedit (dari record database)
        $rawKoordinat = $record?->koordinat;
        if (is_array($rawKoordinat)) {
            $koordinatJs = json_encode($rawKoordinat, JSON_UNESCAPED_UNICODE);
        } elseif (is_string($rawKoordinat) && strlen($rawKoordinat) > 2) {
            $koordinatJs = $rawKoordinat;
        } else {
            $koordinatJs = 'null';
        }

        // Lahan lain yang sudah ada koordinatnya
        $lahanLainData = \App\Models\Lahan::whereNotNull('koordinat')
            ->when($currentId, fn ($q) => $q->where('id', '!=', $currentId))
            ->get(['kode_lahan', 'pemilik', 'kelapa_buah', 'koordinat'])
            ->map(fn ($l) => [
                'nama'         => $l->kode_lahan,
                'pemilik'      => $l->pemilik ?? '',
                'kelapa_buah' => $l->kelapa_buah,
                'geom'         => is_array($l->koordinat) ? $l->koordinat : json_decode($l->koordinat, true),
            ])
            ->filter(fn ($l) => !empty($l['geom']['type']))
            ->values();

        // Path ke field Hidden::make('koordinat') yang sesungguhnya menyimpan nilai
        $mapStatePath = $field->getStatePath();          // e.g. data.koordinat_map
        $pathParts    = explode('.', $mapStatePath);
        array_pop($pathParts);                           // buang 'koordinat_map'
        $koordinatStatePath = implode('.', $pathParts) . '.koordinat';  // → data.koordinat
    @endphp

    <div wire:ignore>

        {{-- Legenda --}}
        <div class="flex flex-wrap gap-4 text-xs text-gray-500 mb-2">
            <span class="flex items-center gap-1">
                <span class="inline-block w-4 h-3 rounded" style="background:#f97316;opacity:0.6;border:1px solid #ea580c"></span>
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
                            + `<br>${lahan.kelapa_buah} pohon`
                            + `<br><small style='color:#b91c1c'>⚠️ Lahan sudah terdaftar</small>`;

                        const styleRef = {
                            color: '#b91c1c', fillColor: '#ef4444',
                            fillOpacity: 0.25, weight: 2, dashArray: '6,4'
                        };

                        L.geoJSON(geom, {
                            style: styleRef,
                            pointToLayer: (f, latlng) => L.circleMarker(latlng, {
                                radius: 9, color: '#b91c1c', fillColor: '#ef4444',
                                fillOpacity: 0.55, weight: 2
                            }),
                            onEachFeature: (f, layer) => {
                                layer.bindPopup(popup);
                            }
                        }).eachLayer(layer => {
                            layer.addTo(refLayer);
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
                                    color: '#ea580c', fillColor: '#f97316', fillOpacity: 0.35
                                },
                            },
                        }
                    });
                    this.map.addControl(drawControl);

                    // Muat koordinat lahan ini (jika edit)
                    if (this.existingData) {
                        try {
                            L.geoJSON(this.existingData, {
                                style: { color: '#ea580c', fillColor: '#f97316', fillOpacity: 0.35, weight: 2 },
                                pointToLayer: (f, latlng) => L.circleMarker(latlng, {
                                    radius: 9, color: '#ea580c', fillColor: '#f97316',
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
                        ? JSON.stringify(data)
                        : null;

                    // Set langsung ke state Livewire (field Hidden::make('koordinat'))
                    this.$wire.set(this.koordinatPath, val);

                    // Juga update hidden input sebagai fallback
                    const input = document.getElementById('hidden-koordinat-input');
                    if (input) {
                        input.value = val ?? '';
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                },

                handleFileUpload(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    const ext = file.name.toLowerCase().split('.').pop();
                    if (ext !== 'zip' && ext !== 'shp') {
                        alert('Error: File harus berformat .zip atau .shp!');
                        if (this.$refs.shpUpload) this.$refs.shpUpload.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const buffer = event.target.result;
                        if (typeof shp === 'undefined') {
                            const script = document.createElement('script');
                            script.src = 'https://unpkg.com/shpjs@latest/dist/shp.js';
                            script.onload = () => this.processShpBuffer(buffer, ext);
                            script.onerror = () => alert('Gagal memuat library shpjs dari koneksi internet.');
                            document.head.appendChild(script);
                        } else {
                            this.processShpBuffer(buffer, ext);
                        }
                    };
                    reader.readAsArrayBuffer(file);
                },

                processShpBuffer(buffer, ext) {
                    let parsePromise;
                    if (ext === 'shp') {
                        // Jika file langsung .shp, parse sebagai shp tunggal (hanya geometri)
                        parsePromise = Promise.resolve(shp.parseShp(buffer)).then(geometries => {
                            // parseShp returns array of geometries. Wrap it in a FeatureCollection
                            if (!Array.isArray(geometries)) geometries = [geometries];
                            return {
                                type: 'FeatureCollection',
                                features: geometries.map(geom => ({
                                    type: 'Feature',
                                    geometry: geom,
                                    properties: {}
                                }))
                            };
                        });
                    } else {
                        // Jika .zip, parse penuh
                        parsePromise = shp(buffer);
                    }

                    parsePromise.then((geojson) => {
                        console.log('SHP parsed result:', geojson);
                        const data = Array.isArray(geojson) ? geojson[0] : geojson;
                        
                        if (!data || !data.features || data.features.length === 0) {
                            alert('Data shapefile kosong atau tidak valid. Pastikan Anda meng-compress (zip) file-file secara langsung, BUKAN di dalam sebuah folder!');
                            return;
                        }

                        // VALIDASI KOORDINAT (Cek apakah UTM atau WGS84)
                        let isWgs84 = true;
                        try {
                            const firstGeom = data.features[0].geometry;
                            let testCoord = null;
                            if (firstGeom.type === 'Polygon') testCoord = firstGeom.coordinates[0][0];
                            else if (firstGeom.type === 'MultiPolygon') testCoord = firstGeom.coordinates[0][0][0];
                            else if (firstGeom.type === 'Point') testCoord = firstGeom.coordinates;

                            if (testCoord && (Math.abs(testCoord[0]) > 180 || Math.abs(testCoord[1]) > 90)) {
                                isWgs84 = false;
                            }
                        } catch(e) {}

                        if (!isWgs84) {
                            alert('⚠️ Peringatan: File Shapefile Anda sepertinya menggunakan sistem koordinat UTM / Lokal (bukan derajat Lat/Long).\n\nPeta ini membutuhkan koordinat WGS84.\n\nSOLUSI: Harap unggah file dalam bentuk .zip yang WAJIB berisi file .prj, .shp, .shx, dan .dbf agar sistem bisa mengkonversi koordinatnya secara otomatis!');
                            if (this.$refs.shpUpload) this.$refs.shpUpload.value = '';
                            return;
                        }

                        this.drawnItems.clearLayers();
                        
                        // Gambar SEMUA fitur yang ada di dalam shapefile
                        const layer = L.geoJSON(data, {
                            style: { color: '#ea580c', fillColor: '#f97316', fillOpacity: 0.35, weight: 2 },
                            pointToLayer: (f, latlng) => L.circleMarker(latlng, {
                                radius: 9, color: '#ea580c', fillColor: '#f97316', fillOpacity: 0.7, weight: 2
                            })
                        });
                        
                        layer.eachLayer(l => {
                            this.drawnItems.addLayer(l);
                        });

                        try {
                            const bounds = this.drawnItems.getBounds();
                            if (bounds.isValid()) {
                                this.map.fitBounds(bounds, { padding: [50, 50] });
                            }
                        } catch(e) {}
                        
                        this.simpanKoordinat();
                        alert('Shapefile berhasil dimuat dan digambar di peta!');
                        
                        if (this.$refs.shpUpload) this.$refs.shpUpload.value = '';

                    }).catch((err) => {
                        console.error('Error parsing SHP:', err);
                        alert('Gagal membaca file.\n\nTIPS:\n1. Pastikan Anda mengunggah file .zip, bukan .shp tunggal.\n2. Saat membuat .zip, blok file .shp, .shx, .dbf, .prj langsung lalu klik kanan -> Send to Compressed (zipped) folder.\n3. JANGAN masukkan file tersebut ke dalam folder baru sebelum di-zip.');
                        if (this.$refs.shpUpload) this.$refs.shpUpload.value = '';
                    });
                }
            }"
            x-init="$nextTick(() => initMap())"
        >
            {{-- Upload SHP --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; margin-bottom: 1rem; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem;">
                <div>
                    <h4 style="font-size: 0.875rem; font-weight: 500; color: #1f2937; margin: 0;">Upload Data Shapefile</h4>
                    <p style="font-size: 0.75rem; color: #6b7280; margin: 0.25rem 0 0 0;">Mendukung format <strong>.zip</strong> (disarankan) atau <strong>.shp</strong> tunggal.</p>
                </div>
                
                <div>
                    <x-filament::button 
                        type="button" 
                        color="primary"
                        icon="heroicon-m-arrow-up-tray"
                        x-on:click="$refs.shpUpload.click()"
                    >
                        Upload SHP / ZIP
                    </x-filament::button>
                </div>
                <input type="file" x-ref="shpUpload" accept=".zip,.shp" style="display: none;" @change="handleFileUpload($event)">
            </div>

            {{-- Hidden input sebagai fallback sync ke field Hidden::make('koordinat') --}}
            <input type="hidden" id="hidden-koordinat-input" wire:model="{{ $koordinatStatePath }}">

            {{-- Peta --}}
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
