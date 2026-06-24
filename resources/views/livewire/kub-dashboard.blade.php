<div class="min-h-screen bg-gray-50">
    <header class="bg-green-700 text-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold">e-Traceability Gula Kelapa</h1>
                <p class="text-green-100 text-sm">Dashboard KUB — Kelompok Usaha Bersama</p>
            </div>
            <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                @csrf
                <button type="submit" class="bg-green-800 hover:bg-green-900 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Logout
                </button>
            </form>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4 py-8 space-y-6">
        {{-- Statistik --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow p-5 border-l-4 border-amber-500">
                <p class="text-xs text-gray-500">Total Setoran</p>
                <p class="text-2xl font-bold">{{ number_format($totalKg, 1) }} <span class="text-sm font-normal text-gray-500">kg</span></p>
            </div>
            <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
                <p class="text-xs text-gray-500">Total Nilai</p>
                <p class="text-2xl font-bold">Rp {{ number_format($totalUang, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
                <p class="text-xs text-gray-500">Jumlah Petani</p>
                <p class="text-2xl font-bold">{{ $jumlahPetani }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-5 border-l-4 border-purple-500">
                <p class="text-xs text-gray-500">Jumlah Pengepul</p>
                <p class="text-2xl font-bold">{{ $jumlahPengepul }}</p>
            </div>
        </div>

        {{-- Rekap per Pengepul --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Rekap per Pengepul</h2>
            @if($rekapPerPengepul->isEmpty())
                <p class="text-gray-400 text-sm">Belum ada data setoran.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2">Pengepul</th>
                                <th class="px-4 py-2 text-right">Jumlah Petani</th>
                                <th class="px-4 py-2 text-right">Jml Setor</th>
                                <th class="px-4 py-2 text-right">Total (kg)</th>
                                <th class="px-4 py-2 text-right">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($rekapPerPengepul as $rekap)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2">
                                    <div class="font-medium">{{ $rekap->pengepul?->nama_koperasi ?? 'Tanpa Pengepul' }}</div>
                                    <div class="text-xs text-gray-400">{{ $rekap->pengepul?->kode_pengepul }}</div>
                                </td>
                                <td class="px-4 py-2 text-right">{{ $rekap->jumlah_petani }}</td>
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

        {{-- Rekap Bulanan --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Rekap Bulanan (Semua Pengepul)</h2>
            @if($rekapBulanan->isEmpty())
                <p class="text-gray-400 text-sm">Belum ada data.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2">Bulan</th>
                                <th class="px-4 py-2 text-right">Petani Aktif</th>
                                <th class="px-4 py-2 text-right">Total (kg)</th>
                                <th class="px-4 py-2 text-right">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($rekapBulanan as $rekap)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium">{{ \Carbon\Carbon::parse($rekap->bulan . '-01')->translatedFormat('F Y') }}</td>
                                <td class="px-4 py-2 text-right">{{ $rekap->jumlah_petani }}</td>
                                <td class="px-4 py-2 text-right font-medium">{{ number_format($rekap->total_kg, 1) }} kg</td>
                                <td class="px-4 py-2 text-right text-green-600 font-medium">Rp {{ number_format($rekap->total_uang, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
