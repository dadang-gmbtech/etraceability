<div class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-3xl mx-auto">

        @if ($notFound)
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                <h1 class="text-xl font-bold text-red-600 mb-2">Trace ID Tidak Ditemukan</h1>
                <p class="text-gray-500">Kode "{{ $trace_id }}" tidak terdaftar dalam sistem kami.</p>
            </div>
        @else
            {{-- ====================== HEADER ====================== --}}
            <div class="bg-emerald-700 text-white rounded-t-xl p-6">
                <p class="text-emerald-100 text-sm">Gula Kelapa Organik — Telusur Produk</p>
                <h1 class="text-2xl font-bold font-mono">{{ $batch->trace_id }}</h1>
                <div class="flex items-center gap-2 mt-2">
                    @if ($batch->is_organik)
                        <span class="bg-white text-emerald-700 px-3 py-1 rounded-full text-xs font-semibold">✓ Bersertifikat Organik</span>
                    @endif
                    <span class="bg-emerald-600 px-3 py-1 rounded-full text-xs capitalize">{{ $batch->status_batch }}</span>
                </div>
            </div>

            <div class="bg-white rounded-b-xl shadow-sm p-6 space-y-8">

                {{-- ====================== RINGKASAN ====================== --}}
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-400">Asal Kebun</p>
                        <p class="font-medium">{{ $batch->petani->desa }}, {{ $batch->petani->kecamatan }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Petani</p>
                        <p class="font-medium">{{ $batch->petani->nama }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Tanggal Sadap</p>
                        <p class="font-medium">{{ $batch->tanggal_sadap?->translatedFormat('d F Y') }}</p>
                    </div>
                    @if ($batch->berat_gula_kg)
                        <div>
                            <p class="text-gray-400">Berat Produk</p>
                            <p class="font-medium">{{ $batch->berat_gula_kg }} kg</p>
                        </div>
                    @endif
                </div>

                {{-- ====================== STATUS SERTIFIKASI ====================== --}}
                @if ($batch->petani->sertifikasiAktif)
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 text-sm">
                        <p class="font-semibold text-emerald-800 mb-1">Sertifikasi Organik Petani</p>
                        <p class="text-emerald-700">No. {{ $batch->petani->sertifikasiAktif->nomor_sertifikat }} — diterbitkan oleh {{ $batch->petani->sertifikasiAktif->lembaga_sertifikasi }}</p>
                        <p class="text-emerald-600 text-xs mt-1">Berlaku hingga {{ $batch->petani->sertifikasiAktif->tanggal_kadaluarsa->translatedFormat('d F Y') }}</p>
                    </div>
                @endif

                {{-- ====================== PETA PERJALANAN ====================== --}}
                <div>
                    <p class="font-semibold text-gray-700 mb-2">Peta Perjalanan Produk</p>
                    <div id="map-trace" wire:ignore style="height: 320px;" class="rounded-lg border"></div>
                </div>

                {{-- ====================== TIMELINE HISTORI ====================== --}}
                <div>
                    <p class="font-semibold text-gray-700 mb-4">Histori Rantai Pasok</p>
                    <ol class="relative border-l-2 border-emerald-200 ml-2">
                        @forelse ($batch->eventTraceability as $event)
                            <li class="mb-6 ml-4">
                                <div class="absolute w-3 h-3 bg-emerald-600 rounded-full -left-[7px] border-2 border-white"></div>
                                <time class="text-xs text-gray-400">{{ $event->waktu_kejadian->translatedFormat('d F Y, H:i') }}</time>
                                <p class="font-medium capitalize text-gray-800">{{ str_replace('_', ' ', $event->tipe_event) }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ ucfirst($event->aktor_tipe) }}
                                    @if ($event->lokasi_nama) — {{ $event->lokasi_nama }} @endif
                                </p>
                                @if ($event->catatan)
                                    <p class="text-xs text-gray-400 mt-1">{{ $event->catatan }}</p>
                                @endif
                            </li>
                        @empty
                            <li class="ml-4 text-gray-400 text-sm">Belum ada histori tercatat.</li>
                        @endforelse
                    </ol>
                </div>
            </div>
        @endif
    </div>
</div>

@if (! $notFound)
@script
<script>
    const map = L.map('map-trace');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const points = [];

    @foreach ($batch->eventTraceability as $event)
        @if ($event->lokasi_lat && $event->lokasi_lng)
            points.push([{{ $event->lokasi_lat }}, {{ $event->lokasi_lng }}]);
            L.marker([{{ $event->lokasi_lat }}, {{ $event->lokasi_lng }}])
                .addTo(map)
                .bindPopup("{{ addslashes(ucfirst(str_replace('_', ' ', $event->tipe_event))) }}<br>{{ addslashes($event->lokasi_nama ?? '') }}");
        @endif
    @endforeach

    if (points.length > 0) {
        L.polyline(points, { color: '#059669', weight: 3, dashArray: '6 6' }).addTo(map);
        map.fitBounds(points, { padding: [30, 30] });
    } else {
        map.setView([-7.7956, 110.3695], 10); // fallback: Yogyakarta
    }
</script>
@endscript
@endif
