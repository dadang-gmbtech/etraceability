<div class="p-6 max-w-7xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Daftar Batch Produksi Gula Kelapa</h1>
            <p class="text-gray-500 mt-1">Kelola batch melalui <a href="/admin" class="text-blue-600 underline">Admin Panel</a> · Klik Traceability untuk melihat asal lahan.</p>
        </div>
        <a href="/admin/batch-produksis/create"
           class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-lg">
            + Tambah Batch
        </a>
    </div>

    {{-- Filter --}}
    <div class="flex gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search"
               type="text"
               placeholder="Cari Trace ID…"
               class="border rounded-lg px-3 py-2 text-sm flex-1 max-w-xs focus:ring-2 focus:ring-green-300 outline-none"/>

        <select wire:model.live="filterStatus"
                class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-300 outline-none">
            <option value="">Semua Status</option>
            <option value="dikumpulkan">Dikumpulkan</option>
            <option value="diproses">Diproses</option>
            <option value="selesai">Selesai</option>
        </select>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl shadow-sm border overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Trace ID</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Tanggal</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Petani</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Via Pengepul</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Berat (kg)</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($batchList as $batch)
                    @php
                        $petaniUnik = $batch->setoranGulas->pluck('petani.nama')->filter()->unique();
                        $statusColor = match($batch->status_batch) {
                            'selesai'     => 'bg-green-100 text-green-700',
                            'diproses'    => 'bg-blue-100 text-blue-700',
                            'dikumpulkan' => 'bg-yellow-100 text-yellow-700',
                            default       => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono font-bold text-blue-700 text-xs bg-blue-50 px-2 py-0.5 rounded">
                                {{ $batch->trace_id }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $batch->tanggal_pengumpulan?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $statusColor }}">
                                {{ ucfirst($batch->status_batch) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            @if ($petaniUnik->isNotEmpty())
                                {{ $petaniUnik->implode(', ') }}
                                <span class="text-xs text-gray-400">({{ $petaniUnik->count() }})</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $batch->pengepul?->nama_koperasi ?? 'Langsung KUB' }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">
                            {{ number_format($batch->berat_total_kg, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('batch.traceability', $batch->trace_id) }}"
                               class="inline-flex items-center gap-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg font-semibold">
                                🗺 Traceability
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                            Belum ada batch produksi.
                            <a href="/admin/batch-produksis/create" class="text-blue-600 underline ml-1">Buat batch baru</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $batchList->links() }}
    </div>
</div>
