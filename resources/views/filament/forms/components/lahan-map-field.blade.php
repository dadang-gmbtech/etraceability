<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $record = $getRecord();
        $rawKoordinat = $record?->koordinat;
        if (is_array($rawKoordinat)) {
            $koordinatJs = json_encode($rawKoordinat, JSON_UNESCAPED_UNICODE);
        } elseif (is_string($rawKoordinat) && strlen($rawKoordinat) > 2) {
            $koordinatJs = $rawKoordinat;
        } else {
            $koordinatJs = 'null';
        }

        // Dapatkan state path dinamis untuk field koordinat (bukan koordinat_map)
        $mapStatePath = $field->getStatePath();
        $pathParts = explode('.', $mapStatePath);
        array_pop($pathParts); // hapus "koordinat_map"
        $koordinatStatePath = implode('.', $pathParts) . '.koordinat';
    @endphp

    <div
        wire:ignore
        x-data="{
            map: null,
            drawnItems: null,
            existingData: {{ $koordinatJs }},
            koordinatPath: '{{ $koordinatStatePath }}',
            initMap() {
                const el = this.$refs.mapContainer;
                if (!el || el._leafletMap) return;

                this.map = L.map(el).setView([-7.7956, 110.3695], 13);
                el._leafletMap = this.map;

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(this.map);

                this.drawnItems = new L.FeatureGroup();
                this.map.addLayer(this.drawnItems);

                const drawControl = new L.Control.Draw({
                    edit: {
                        featureGroup: this.drawnItems,
                        remove: true,
                    },
                    draw: {
                        polyline: false,
                        circle: false,
                        rectangle: false,
                        circlemarker: false,
                        marker: { title: 'Tandai titik lokasi' },
                        polygon: {
                            allowIntersection: false,
                            showArea: true,
                            title: 'Gambar batas area lahan',
                        },
                    }
                });
                this.map.addControl(drawControl);

                // Muat koordinat yang sudah ada
                if (this.existingData) {
                    try {
                        const gj = L.geoJSON(this.existingData);
                        gj.eachLayer(layer => this.drawnItems.addLayer(layer));
                        const b = this.drawnItems.getBounds();
                        if (b.isValid()) {
                            this.map.fitBounds(b, { padding: [40, 40] });
                        }
                    } catch (e) {
                        console.warn('GeoJSON load error:', e);
                    }
                }

                this.map.on(L.Draw.Event.CREATED, (e) => {
                    this.drawnItems.clearLayers();
                    this.drawnItems.addLayer(e.layer);
                    this.simpanKoordinat();
                });
                this.map.on(L.Draw.Event.EDITED, () => this.simpanKoordinat());
                this.map.on(L.Draw.Event.DELETED, () => this.simpanKoordinat());
            },
            simpanKoordinat() {
                const data = this.drawnItems.toGeoJSON();
                const val = data.features.length > 0
                    ? JSON.stringify(data.features[0].geometry)
                    : null;
                this.$wire.set(this.koordinatPath, val);
            }
        }"
        x-init="$nextTick(() => initMap())"
    >
        <div
            x-ref="mapContainer"
            style="height: 450px; width: 100%; z-index: 1; border-radius: 0.5rem; border: 1px solid #e2e8f0;"
        ></div>
        <p class="text-xs text-gray-400 mt-2">
            Klik ikon <strong>📍 marker</strong> untuk menandai satu titik, atau
            <strong>poligon</strong> untuk menggambar area lahan. Klik titik pertama untuk menutup poligon.
        </p>
    </div>
</x-dynamic-component>
