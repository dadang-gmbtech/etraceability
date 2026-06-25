<div class="min-h-screen bg-gray-50">

    {{-- Header --}}
    <header class="bg-amber-600 text-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold">e-Traceability Gula Kelapa</h1>
                <p class="text-amber-100 text-sm">
                    Dashboard Petani
                    @if($petani) — {{ $petani->nama }}
                        <span class="opacity-70">({{ $petani->kode_petani }})</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('auth.logout') }}"
               onclick="return confirm('Yakin ingin logout?')"
               class="bg-amber-700 hover:bg-amber-800 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Logout
            </a>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4 py-8 space-y-6">

        {{-- Notif belum dikonfigurasi --}}
        @if($belumDikonfigurasi ?? false)
            <div class="bg-yellow-50 border border-yellow-300 rounded-xl p-6 text-yellow-800">
                <p class="font-semibold text-base">Akun Anda belum dikonfigurasi</p>
                <p class="text-sm mt-1">Admin belum menautkan akun Anda ke data petani. Hubungi admin untuk melakukan konfigurasi.</p>
            </div>
        @else

        {{-- ===== STATISTIK ===== --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow p-5 border-l-4 border-amber-500">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Total Setoran</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($totalKg, 1) }}</p>
                <p class="text-xs text-gray-400">kilogram</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Hasil Penjualan</p>
                <p class="text-lg font-bold text-gray-800 mt-1">Rp {{ number_format($totalUang, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400">total diterima</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Transaksi</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $jumlahSetoran }}</p>
                <p class="text-xs text-gray-400">kali setoran</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5 border-l-4 border-purple-500">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Lahan Dikelola</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $lahans->count() }}</p>
                <p class="text-xs text-gray-400">{{ $lahans->sum('jumlah_pohon') }} pohon</p>
            </div>
        </div>

        {{-- ===== LAHAN YANG DIKELOLA ===== --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Lahan yang Dikelola</h2>

            @if($lahans->isEmpty())
                <p class="text-gray-400 text-sm">Belum ada data lahan terdaftar.</p>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Tabel lahan --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-2">Nama Lahan</th>
                                    <th class="px-4 py-2">Pemilik</th>
                                    <th class="px-4 py-2 text-right">Jumlah Pohon</th>
                                    <th class="px-4 py-2 text-center">Tipe</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($lahans as $lahan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $lahan->nama_lahan }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $lahan->pemilik ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right font-medium">{{ number_format($lahan->jumlah_pohon) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded text-xs
                                            {{ $lahan->jenis_geometri === 'polygon' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ $lahan->jenis_geometri === 'polygon' ? 'Polygon' : 'Titik' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 text-xs font-medium text-gray-600">
                                <tr>
                                    <td colspan="2" class="px-4 py-2">Total</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($lahans->sum('jumlah_pohon')) }} pohon</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Peta mini lahan --}}
                    <div>
                        <div id="peta-lahan" class="w-full rounded-lg border border-gray-200" style="height: 280px;"></div>
                        <p class="text-xs text-gray-400 mt-1 text-center">Peta batas lahan</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ===== REKAP SETORAN BULANAN ===== --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Rekap Setoran Bulanan</h2>
            @if($rekapBulanan->isEmpty())
                <p class="text-gray-400 text-sm">Belum ada data setoran.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2">Bulan</th>
                                <th class="px-4 py-2 text-right">Jumlah Setor</th>
                                <th class="px-4 py-2 text-right">Total Setoran (kg)</th>
                                <th class="px-4 py-2 text-right">Hasil Penjualan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($rekapBulanan as $rekap)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium">
                                    {{ \Carbon\Carbon::parse($rekap->bulan . '-01')->translatedFormat('F Y') }}
                                </td>
                                <td class="px-4 py-2 text-right">{{ $rekap->jumlah_setor }}x</td>
                                <td class="px-4 py-2 text-right font-medium">{{ number_format($rekap->total_kg, 1) }} kg</td>
                                <td class="px-4 py-2 text-right text-green-600 font-medium">
                                    Rp {{ number_format($rekap->total_uang, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ===== RIWAYAT SETORAN ===== --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Riwayat Setoran (10 Terakhir)</h2>
            @if($setoranTerakhir->isEmpty())
                <p class="text-gray-400 text-sm">Belum ada data setoran.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2">Tanggal</th>
                                <th class="px-4 py-2">No. Batch</th>
                                <th class="px-4 py-2">Jenis Produk</th>
                                <th class="px-4 py-2 text-right">Setoran (kg)</th>
                                <th class="px-4 py-2 text-right">Hasil Penjualan</th>
                                <th class="px-4 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($setoranTerakhir as $setor)
                            <tr class="hover:bg-gray-50 {{ $setor->is_anomali ? 'bg-red-50' : '' }}">
                                <td class="px-4 py-2 whitespace-nowrap">
                                    {{ $setor->tanggal_setor->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 font-mono text-xs text-gray-500">
                                    {{ $setor->batchProduksi?->trace_id ?? '-' }}
                                </td>
                                <td class="px-4 py-2 text-gray-600">
                                    {{ $setor->jenis_produk ?? 'gula semut' }}
                                </td>
                                <td class="px-4 py-2 text-right font-medium">
                                    {{ number_format($setor->berat_kg, 1) }} kg
                                </td>
                                <td class="px-4 py-2 text-right text-green-600">
                                    Rp {{ number_format($setor->total_harga, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    @if($setor->is_anomali)
                                        <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs font-medium">Anomali</span>
                                    @else
                                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-medium">Normal</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @endif {{-- end belumDikonfigurasi --}}
    </div>

    {{-- Peta Lahan Script --}}
    @if(!($belumDikonfigurasi ?? false) && $lahans->isNotEmpty())
    @php
        $lahanGeoData = $lahans->filter(fn($l) => !empty($l->koordinat))->values();
    @endphp
    @if($lahanGeoData->isNotEmpty())
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var map = L.map('peta-lahan');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var bounds = [];
        var lahanData = @json($lahanGeoData->map(fn($l) => [
            'nama'     => $l->nama_lahan,
            'tipe'     => $l->jenis_geometri,
            'koordinat'=> $l->koordinat,
        ]));

        lahanData.forEach(function(lahan) {
            if (!lahan.koordinat || lahan.koordinat.length === 0) return;

            if (lahan.tipe === 'polygon') {
                var coords = lahan.koordinat.map(function(c) {
                    return [c.lat ?? c[0], c.lng ?? c[1]];
                });
                var polygon = L.polygon(coords, {
                    color: '#ea580c',
                    fillColor: '#f97316',
                    fillOpacity: 0.35,
                    weight: 2
                }).addTo(map);
                polygon.bindTooltip(lahan.nama, { permanent: false });
                coords.forEach(function(c) { bounds.push(c); });
            } else {
                var lat = lahan.koordinat.lat ?? lahan.koordinat[0];
                var lng = lahan.koordinat.lng ?? lahan.koordinat[1];
                L.circleMarker([lat, lng], {
                    color: '#ea580c', fillColor: '#f97316',
                    fillOpacity: 0.8, radius: 8
                }).addTo(map).bindTooltip(lahan.nama);
                bounds.push([lat, lng]);
            }
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [20, 20] });
        } else {
            map.setView([-7.5, 109.2], 11);
        }
    });
    </script>
    @else
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var map = L.map('peta-lahan').setView([-7.5, 109.2], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
    });
    </script>
    @endif
    @endif

</div>
