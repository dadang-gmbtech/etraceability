<div class="p-6 max-w-6xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Data Petani Gula Kelapa</h1>
            <p class="text-gray-500 mt-1">Kelola data petani melalui <a href="/admin" class="text-blue-600 underline">Admin Panel</a>.</p>
        </div>
        <a href="/admin/petanis/create"
           class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-lg">
            + Tambah Petani
        </a>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search"
               type="text"
               placeholder="Cari nama, kode, atau desa petani…"
               class="border rounded-lg px-3 py-2 text-sm w-full max-w-sm focus:ring-2 focus:ring-green-300 outline-none"/>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl shadow-sm border overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Kode</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Nama Petani</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">No. HP</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Desa / Kecamatan</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Lahan</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Total Pohon</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($petaniList as $petani)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-semibold">
                                {{ $petani->kode_petani }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $petani->nama }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $petani->no_hp ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ implode(', ', array_filter([$petani->desa, $petani->kecamatan])) ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-700">
                            {{ $petani->lahans_count }}
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-green-700">
                            {{ $petani->lahans()->sum('jumlah_pohon') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($petani->aktif)
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded font-semibold">Aktif</span>
                            @else
                                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('petani.qrcode', $petani->kode_petani) }}" target="_blank"
                                   class="text-xs bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded font-semibold">
                                    QR
                                </a>
                                <a href="/admin/petanis/{{ $petani->id }}/edit"
                                   class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1 rounded font-semibold">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-400">
                            Belum ada data petani.
                            <a href="/admin/petanis/create" class="text-blue-600 underline ml-1">Tambah petani</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $petaniList->links() }}
    </div>
</div>
