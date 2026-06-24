<div class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <header class="bg-amber-600 text-white shadow">
        <div class="max-w-5xl mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold">e-Traceability Gula Kelapa</h1>
                <p class="text-amber-100 text-sm">Dashboard Petani — {{ auth()->user()->petani?->nama }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="bg-amber-700 hover:bg-amber-800 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Logout
                </button>
            </form>
        </div>
    </header>

    <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">
        {{-- Statistik ringkas --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow p-5 border-l-4 border-amber-500">
                <p class="text-sm text-gray-500">Total Setoran</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($totalKg, 1) }} <span class="text-base font-normal text-gray-500">kg</span></p>
            </div>
            <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
                <p class="text-sm text-gray-500">Total Pendapatan</p>
                <p class="text-3xl font-bold text-gray-800">Rp {{ number_format($totalUang, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
                <p class="text-sm text-gray-500">Jumlah Setoran</p>
                <p class="text-3xl font-bold text-gray-800">{{ $setoranTerakhir->count() }} <span class="text-base font-normal text-gray-500">transaksi (10 terakhir)</span></p>
            </div>
        </div>

        {{-- Rekap Bulanan --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Rekap Bulanan</h2>
            @if($rekapBulanan->isEmpty())
                <p class="text-gray-400 text-sm">Belum ada data setoran.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2">Bulan</th>
                                <th class="px-4 py-2 text-right">Jumlah Setor</th>
                                <th class="px-4 py-2 text-right">Total (kg)</th>
                                <th class="px-4 py-2 text-right">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($rekapBulanan as $rekap)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium">{{ \Carbon\Carbon::parse($rekap->bulan . '-01')->translatedFormat('F Y') }}</td>
                                <td class="px-4 py-2 text-right">{{ $rekap->jumlah_setor }}x</td>
                                <td class="px-4 py-2 text-right font-medium">{{ number_format($rekap->total_kg, 1) }} kg</td>
                                <td class="px-4 py-2 text-right text-green-600 font-medium">Rp {{ number_format($rekap->total_uang, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Riwayat Setoran --}}
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
                                <th class="px-4 py-2 text-right">Berat (kg)</th>
                                <th class="px-4 py-2 text-right">Pendapatan</th>
                                <th class="px-4 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($setoranTerakhir as $setor)
                            <tr class="hover:bg-gray-50 {{ $setor->is_anomali ? 'bg-red-50' : '' }}">
                                <td class="px-4 py-2">{{ $setor->tanggal_setor->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 text-xs font-mono text-gray-500">{{ $setor->batchProduksi?->trace_id ?? '-' }}</td>
                                <td class="px-4 py-2 text-right font-medium">{{ number_format($setor->berat_kg, 1) }}</td>
                                <td class="px-4 py-2 text-right text-green-600">Rp {{ number_format($setor->total_harga, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-center">
                                    @if($setor->is_anomali)
                                        <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs">Anomali</span>
                                    @else
                                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs">Normal</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
