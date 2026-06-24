<div class="min-h-screen bg-gray-50">
    <header class="bg-blue-700 text-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold">e-Traceability Gula Kelapa</h1>
                <p class="text-blue-100 text-sm">Dashboard Pengepul — {{ auth()->user()->pengepul?->nama_koperasi }}</p>
            </div>
            <a href="{{ route('auth.logout') }}"
               class="bg-blue-800 hover:bg-blue-900 text-white px-4 py-2 rounded-lg text-sm font-medium"
               onclick="return confirm('Yakin ingin logout?')">
                Logout
            </a>
        </div>
    </header>

    <div class="max-w-6xl mx-auto px-4 py-8 space-y-6">
        @if($belumDikonfigurasi ?? false)
        <div class="bg-yellow-50 border border-yellow-300 rounded-xl p-5 text-yellow-800">
            <p class="font-semibold">Akun Anda belum dikonfigurasi</p>
            <p class="text-sm mt-1">Admin belum menautkan akun Anda ke data pengepul. Hubungi admin untuk melakukan konfigurasi.</p>
        </div>
        @endif

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
                <p class="text-xs text-gray-500">Rekap Bulan Ini</p>
                @php
                    $bulanIni = $rekapBulanan->firstWhere('bulan', now()->format('Y-m'));
                @endphp
                <p class="text-2xl font-bold">{{ number_format($bulanIni?->total_kg ?? 0, 1) }} <span class="text-sm font-normal text-gray-500">kg</span></p>
            </div>
        </div>

        {{-- Rekap per Petani --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Rekap Setoran per Petani</h2>
            @if($rekapPerPetani->isEmpty())
                <p class="text-gray-400 text-sm">Belum ada data setoran.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2">Petani</th>
                                <th class="px-4 py-2 text-right">Jml Setor</th>
                                <th class="px-4 py-2 text-right">Total (kg)</th>
                                <th class="px-4 py-2 text-right">Total Nilai</th>
                                <th class="px-4 py-2">Setor Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($rekapPerPetani as $rekap)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2">
                                    <div class="font-medium">{{ $rekap->petani?->nama ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">{{ $rekap->petani?->kode_petani }}</div>
                                </td>
                                <td class="px-4 py-2 text-right">{{ $rekap->jumlah_setor }}x</td>
                                <td class="px-4 py-2 text-right font-medium">{{ number_format($rekap->total_kg, 1) }} kg</td>
                                <td class="px-4 py-2 text-right text-green-600 font-medium">Rp {{ number_format($rekap->total_uang, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-xs text-gray-500">{{ $rekap->setor_terakhir ? \Carbon\Carbon::parse($rekap->setor_terakhir)->format('d/m/Y') : '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Rekap Bulanan --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Rekap Bulanan</h2>
            @if($rekapBulanan->isEmpty())
                <p class="text-gray-400 text-sm">Belum ada data.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2">Bulan</th>
                                <th class="px-4 py-2 text-right">Jumlah Petani</th>
                                <th class="px-4 py-2 text-right">Total (kg)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($rekapBulanan as $rekap)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium">{{ \Carbon\Carbon::parse($rekap->bulan . '-01')->translatedFormat('F Y') }}</td>
                                <td class="px-4 py-2 text-right">{{ $rekap->jumlah_petani }} petani</td>
                                <td class="px-4 py-2 text-right font-medium">{{ number_format($rekap->total_kg, 1) }} kg</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
