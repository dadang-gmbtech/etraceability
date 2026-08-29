<x-filament-widgets::widget>
<style>
.adm-stat-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:1rem;margin-bottom:1rem}
@media(max-width:768px){.adm-stat-grid{grid-template-columns:repeat(2,1fr)}}
.adm-stat-card{background:#fff;border-radius:.75rem;padding:1rem;box-shadow:0 1px 3px rgba(0,0,0,.08);border-left:4px solid #ccc}
.dark .adm-stat-card{background:#1f2937}
.adm-stat-card .lbl{font-size:.75rem;color:#9ca3af;margin:0}
.adm-stat-card .val{font-size:1.5rem;font-weight:700;color:#111827;margin:0}
.dark .adm-stat-card .val{color:#f9fafb}
.adm-map-card{background:#fff;border-radius:.75rem;box-shadow:0 1px 3px rgba(0,0,0,.08);overflow:hidden}
.dark .adm-map-card{background:#1f2937}
.adm-map-head{padding:.875rem 1.25rem .5rem}
.adm-map-head h2{font-size:1rem;font-weight:600;color:#111827;margin:0 0 .2rem}
.dark .adm-map-head h2{color:#f9fafb}
.adm-map-head p{font-size:.75rem;color:#9ca3af;margin:0}
.adm-layer-row{display:flex;flex-wrap:wrap;align-items:center;gap:.75rem;padding:.5rem 1.25rem .75rem}
.adm-layer-row .ttl{font-size:.7rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em}
.adm-layer-row label{display:flex;align-items:center;gap:.375rem;font-size:.875rem;color:#374151;cursor:pointer}
.dark .adm-layer-row label{color:#d1d5db}
.adm-dot{display:inline-block;width:10px;height:10px;border-radius:50%}
</style>

    {{-- Statistik --}}
    <div class="adm-stat-grid">
        <div class="adm-stat-card" style="border-color:#10b981">
            <p class="lbl">Petani Aktif</p>
            <p class="val">{{ $statistik['total_petani'] }}</p>
        </div>
        <div class="adm-stat-card" style="border-color:#14b8a6">
            <p class="lbl">Total Lahan</p>
            <p class="val">{{ $statistik['total_lahan'] }}</p>
        </div>
        <div class="adm-stat-card" style="border-color:#84cc16">
            <p class="lbl">Total Pohon Kelapa</p>
            <p class="val">{{ number_format($statistik['total_pohon']) }}</p>
        </div>
        <div class="adm-stat-card" style="border-color:#3b82f6">
            <p class="lbl">Pengepul / Koperasi</p>
            <p class="val">{{ $statistik['total_pengepul'] }}</p>
        </div>
        <div class="adm-stat-card" style="border-color:#a855f7">
            <p class="lbl">Total Batch</p>
            <p class="val">{{ $statistik['total_batch'] }}</p>
        </div>
        <div class="adm-stat-card" style="border-color:#f97316">
            <p class="lbl">Perangkat IoT</p>
            <p class="val">{{ $statistik['total_device'] }}</p>
        </div>
    </div>

    {{-- Peta --}}
    <div class="adm-map-card">
        <div class="adm-map-head">
            <h2>Peta Sebaran Lahan &amp; Perangkat IoT</h2>
            <p>Visualisasi spasial lahan kelapa dan perangkat IoT dalam rantai pasok gula kelapa.</p>
        </div>

        <div class="adm-layer-row">
            <span class="ttl">Tampilkan Layer:</span>
            <label>
                <input type="checkbox" checked onchange="admToggleLayer('persil',this.checked)">
                <span class="adm-dot" style="background:#93c5fd;border:2px solid #1d4ed8;"></span>
                Persil/Lahan ({{ $statistik['total_persil'] }})
            </label>
            <label>
                <input type="checkbox" checked onchange="admToggleLayer('koordinat',this.checked)">
                <span class="adm-dot" style="background:#f97316"></span>
                Koordinat Lahan ({{ $statistik['total_koordinat'] }})
            </label>
            <label>
                <input type="checkbox" checked onchange="admToggleLayer('pengepul',this.checked)">
                <span class="adm-dot" style="background:#3b82f6"></span>
                Pengepul ({{ $statistik['total_pengepul'] }})
            </label>
            <label>
                <input type="checkbox" checked onchange="admToggleLayer('iot',this.checked)">
                <span class="adm-dot" style="background:#2563eb"></span>
                Perangkat IoT ({{ $statistik['total_device'] }})
            </label>
        </div>

        <div wire:ignore>
            <div id="adm-map-sebaran" style="width:100%;height:640px;display:block;"></div>
        </div>
    </div>

</x-filament-widgets::widget>

@script
<script>
var _admLahanGeo    = @json($lahanGeo->values()->all());
var _admPengepulGeo = @json($pengepulGeo->values()->all());
var _admDeviceGeo   = @json($deviceGeo->values()->all());

(function admInitMap() {
    var el = document.getElementById('adm-map-sebaran');
    if (!window.L || !el) return setTimeout(admInitMap, 150);

    if (window._admMap) { try { window._admMap.remove(); } catch(e) {} window._admMap = null; }

    var layers = { persil: L.layerGroup(), koordinat: L.layerGroup(), pengepul: L.layerGroup(), iot: L.layerGroup() };
    var map    = L.map(el, {zoomControl:true}).setView([-7.281166, 109.286804], 11);
    window._admMap        = map;
    window.admToggleLayer = function(n,s){ s ? layers[n].addTo(map) : map.removeLayer(layers[n]); };

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution:'&copy; OpenStreetMap contributors', maxZoom:19
    }).addTo(map);
    layers.persil.addTo(map); layers.koordinat.addTo(map); layers.pengepul.addTo(map); layers.iot.addTo(map);

    var bounds = [];

    (_admLahanGeo||[]).forEach(function(lahan) {
        if (!lahan.geom) return;
        var pop = '<b>'+(lahan.kode||'?')+'</b><br>Penderes: '+lahan.petani+'<br>Pemilik: '+lahan.pemilik;
        var isPolygon = lahan.isPolygon;
        function addG(g) {
            if (!g||!g.type) return;
            if (g.type==='FeatureCollection') { (g.features||[]).forEach(function(f){addG(f.geometry||f);}); }
            else if (g.type==='Feature') { addG(g.geometry); }
            else if (g.type==='GeometryCollection') { (g.geometries||[]).forEach(addG); }
            else if (g.type==='Point') {
                var lat=g.coordinates[1],lng=g.coordinates[0];
                layers.koordinat.addLayer(L.circleMarker([lat,lng],{radius:5,color:'#ea580c',fillColor:'#f97316',fillOpacity:.8,weight:1}).bindPopup(pop));
                bounds.push([lat,lng]);
            } else if (g.type==='Polygon') {
                var c=g.coordinates[0].map(function(x){return[x[1],x[0]];});
                layers.persil.addLayer(L.polygon(c,{color:'#1d4ed8',fillColor:'#93c5fd',fillOpacity:.4,weight:1.5}).bindPopup(pop));
                c.forEach(function(x){bounds.push(x);});
            } else if (g.type==='MultiPolygon') {
                g.coordinates.forEach(function(p){
                    var c=p[0].map(function(x){return[x[1],x[0]];});
                    layers.persil.addLayer(L.polygon(c,{color:'#1d4ed8',fillColor:'#93c5fd',fillOpacity:.4,weight:1.5}).bindPopup(pop));
                    c.forEach(function(x){bounds.push(x);});
                });
            }
        }
        addG(lahan.geom);
    });

    (_admPengepulGeo||[]).forEach(function(p) {
        var ic=L.divIcon({className:'',iconSize:[22,22],iconAnchor:[11,22],
            html:'<div style="width:0;height:0;border-left:11px solid transparent;border-right:11px solid transparent;border-bottom:22px solid #3b82f6"></div>'});
        layers.pengepul.addLayer(L.marker([p.lat,p.lng],{icon:ic}).bindPopup('<b>'+p.nama+'</b>'));
        bounds.push([p.lat,p.lng]);
    });

    (_admDeviceGeo||[]).forEach(function(d) {
        var c=d.active?'#2563eb':'#93c5fd';
        var ic=L.divIcon({className:'',iconSize:[14,14],iconAnchor:[7,7],
            html:'<div style="width:14px;height:14px;border-radius:50%;background:'+c+';border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.4)"></div>'});
        layers.iot.addLayer(L.marker([d.lat,d.lng],{icon:ic}).bindPopup('<b>'+d.name+'</b><br>Lahan: '+d.lahan));
        bounds.push([d.lat,d.lng]);
    });

    if (bounds.length) { try { map.fitBounds(bounds,{padding:[30,30],maxZoom:16}); } catch(e){} }
    setTimeout(function(){ map.invalidateSize(); }, 300);
})();
</script>
@endscript
