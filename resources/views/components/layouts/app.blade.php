<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'E-Traceability Gula Kelapa Organik' }}</title>

    {{-- Tailwind via CDN untuk eksplorasi awal.
         Untuk produksi, sebaiknya pakai Vite build (npm run build) agar lebih ringan. --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Leaflet CSS & JS untuk peta GIS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @livewireStyles
</head>
<body class="bg-gray-50">

    {{-- ====================== NAVBAR SEDERHANA ====================== --}}
    <nav class="bg-emerald-800 text-white px-6 py-4 flex justify-between items-center">
        <span class="font-bold">🥥 E-Traceability Gula Kelapa Organik</span>
        <div class="flex items-center gap-4 text-sm">
            <a href="{{ route('peta.sebaran') }}" class="hover:text-emerald-200">Peta Sebaran</a>
            <a href="{{ route('petani.index') }}" class="hover:text-emerald-200">Data Petani</a>
            <a href="{{ route('batch.index') }}" class="hover:text-emerald-200">Batch Produksi</a>
            <a href="{{ route('rute.index') }}" class="hover:text-emerald-200">Rute Distribusi</a>

            @auth
                <span class="text-emerald-300 border-l border-emerald-600 pl-4">
                    {{ auth()->user()->name }}
                </span>
                <a href="{{ route('auth.logout') }}"
                   onclick="return confirm('Yakin ingin logout?')"
                   class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-semibold">
                    Logout
                </a>
            @else
                <a href="{{ route('filament.admin.auth.login') }}"
                   class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1 rounded text-xs font-semibold">
                    Login
                </a>
            @endauth
        </div>
    </nav>

    <main>
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
