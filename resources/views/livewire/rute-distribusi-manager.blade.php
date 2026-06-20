<div class="p-6 max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Rute Distribusi</h1>
        <button wire:click="bukaForm" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700">
            + Tambah Rute Baru
        </button>
    </div>

    @if (session('sukses'))
        <div class="bg-emerald-100 text-emerald-800 px-4 py-3 rounded-lg mb-4">
            {{ session('sukses') }}
        </div>
    @endif

    {{-- ====================== FORM ====================== --}}
    @if ($showForm)
        <div class="bg-white border rounded-xl p-6 mb-6 shadow-sm">
            <h2 class="text-lg font-semibold mb-4">{{ $isEdit ? 'Edit Rute Distribusi' : 'Tambah Rute Baru' }}</h2>

            <form wire:submit.prevent="simpan" class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Batch Produksi</label>
                    <select wire:model="batch_produksi_id" class="w-full border rounded-lg px-3 py-2">
                        <option value="">-- Pilih Batch --</option>
                        @foreach ($batchOptions as $b)
                            <option value="{{ $b->id }}">{{ $b->trace_id }} ({{ $b->petani->nama ?? '-' }})</option>
                        @endforeach
                    </select>
                    @error('batch_produksi_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Distributor / Kurir</label>
                    <select wire:model="distributor_id" class="w-full border rounded-lg px-3 py-2">
                        <option value="">-- Pilih Distributor --</option>
                        @foreach ($distributorOptions as $d)
                            <option value="{{ $d->id }}">{{ $d->nama_perusahaan }}</option>
                        @endforeach
                    </select>
                    @error('distributor_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Asal</label>
                    <input type="text" wire:model="asal" class="w-full border rounded-lg px-3 py-2" placeholder="mis. Gudang Koperasi A">
                    @error('asal') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Tujuan</label>
                    <input type="text" wire:model="tujuan" class="w-full border rounded-lg px-3 py-2" placeholder="mis. Supermarket B Jakarta">
                    @error('tujuan') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Berangkat</label>
                    <input type="datetime-local" wire:model="waktu_berangkat" class="w-full border rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Tiba</label>
                    <input type="datetime-local" wire:model="waktu_tiba" class="w-full border rounded-lg px-3 py-2">
                </div>

                {{-- Map Container --}}
                <div class="md:col-span-2 mt-4" wire:ignore>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Jalur Rute</label>
                    <p class="text-xs text-gray-500 mb-2">Klik pada peta secara berurutan untuk menggambar garis jalur rute. Klik ganda (double-click) untuk menyelesaikan garis.</p>
                    <div id="map-rute" style="height: 350px; z-index: 1;" class="rounded-lg border"></div>
                    @error('koordinat_jalur') <span class="text-red-500 text-sm block mt-1">{{ $message }}</span> @enderror
                    
                    <button type="button" id="btn-clear-route" class="mt-2 text-sm text-red-600 hover:text-red-800 underline">
                        Hapus Jalur (Reset)
                    </button>
                </div>

                <div class="md:col-span-2 flex gap-3 mt-4">
                    <button type="submit" class="bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700">
                        Simpan Rute
                    </button>
                    <button type="button" wire:click="batal" class="bg-gray-200 text-gray-700 px-5 py-2 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- ====================== TABEL DATA ====================== --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-4 py-3">Trace ID</th>
                    <th class="px-4 py-3">Distributor</th>
                    <th class="px-4 py-3">Rute</th>
                    <th class="px-4 py-3">Berangkat</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($ruteList as $rute)
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs">{{ $rute->batchProduksi->trace_id ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $rute->distributor->nama_perusahaan ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-gray-800">{{ $rute->asal }}</span>
                            <br>↓<br>
                            <span class="font-medium text-emerald-700">{{ $rute->tujuan }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $rute->waktu_berangkat ? $rute->waktu_berangkat->format('d/m/Y H:i') : '-' }}</td>
                        <td class="px-4 py-3 space-x-2">
                            <button wire:click="edit({{ $rute->id }})" class="text-blue-600 hover:underline">Edit</button>
                            <button wire:click="hapus({{ $rute->id }})"
                                    wire:confirm="Yakin ingin menghapus rute ini?"
                                    class="text-red-600 hover:underline">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada data rute distribusi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $ruteList->links() }}
    </div>

    {{-- Skrip Peta --}}
    @assets
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endassets

    @script
    <script>
        let map;
        let polyline;
        let latlngs = [];

        function initMap() {
            if (map) return;
            
            map = L.map('map-rute').setView([-7.250445, 112.768845], 8); // Default center (Jawa Timur)
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            polyline = L.polyline([], {color: 'blue', weight: 4}).addTo(map);

            map.on('click', function(e) {
                latlngs.push([e.latlng.lat, e.latlng.lng]);
                polyline.setLatLngs(latlngs);
                
                // Update Livewire property
                $wire.set('koordinat_jalur', latlngs, false);
            });

            document.getElementById('btn-clear-route').addEventListener('click', function() {
                latlngs = [];
                polyline.setLatLngs([]);
                $wire.set('koordinat_jalur', []);
            });
        }

        // Jalankan saat form muncul
        if (document.getElementById('map-rute')) {
            initMap();
        }

        // Listeners for Livewire events
        Livewire.on('loadRoute', (data) => {
            setTimeout(() => {
                if (!map) initMap();
                latlngs = data.koordinat || [];
                polyline.setLatLngs(latlngs);
                if (latlngs.length > 0) {
                    map.fitBounds(polyline.getBounds());
                }
            }, 100);
        });

        Livewire.on('resetMap', () => {
            latlngs = [];
            if (polyline) polyline.setLatLngs([]);
        });

        // Initialize map when DOM updates (if form is toggled)
        let observer = new MutationObserver(() => {
            if (document.getElementById('map-rute') && !map) {
                initMap();
            } else if (!document.getElementById('map-rute')) {
                // Cleanup map instance if form is removed
                if (map) {
                    map.remove();
                    map = null;
                    polyline = null;
                }
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    </script>
    @endscript
</div>
