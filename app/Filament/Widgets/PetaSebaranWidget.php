<?php

namespace App\Filament\Widgets;

use App\Models\BatchProduksi;
use App\Models\Device;
use App\Models\Lahan;
use App\Models\Petani;
use App\Models\Pengepul;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class PetaSebaranWidget extends Widget
{
    protected string $view = 'filament.widgets.peta-sebaran-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1;

    protected function getViewData(): array
    {
        $lahans   = Lahan::with('petani')->get();
        $devices  = Device::with('lahan.petani')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        // Pre-process lahan geo data
        $lahanGeo = $lahans->map(function ($l) {
            $geom = $l->koordinat; // already cast to array by model
            if (is_string($geom)) {
                $geom = json_decode($geom, true);
            }
            return [
                'kode'    => $l->kode_lahan ?? '—',
                'petani'  => $l->petani?->nama ?? '—',
                'pemilik' => $l->pemilik ?? '—',
                'buah'    => (int) ($l->kelapa_buah ?? 0),
                'geom'    => $geom,
            ];
        })->filter(fn ($l) => !empty($l['geom']))->values();

        // Pre-process pengepul — guard PostGIS query with try/catch
        $pengepulGeo = collect();
        try {
            $rows = DB::select(
                'SELECT id, nama_koperasi, ST_Y(lokasi_gudang) as lat, ST_X(lokasi_gudang) as lng
                 FROM pengepul
                 WHERE lokasi_gudang IS NOT NULL'
            );
            $pengepulGeo = collect($rows)->map(fn ($r) => [
                'nama' => $r->nama_koperasi,
                'lat'  => (float) $r->lat,
                'lng'  => (float) $r->lng,
            ]);
        } catch (\Throwable $e) {
            // PostGIS tidak tersedia atau kolom tidak ada — skip pengepul layer
        }

        // Pre-process device geo data
        $deviceGeo = $devices->map(fn ($d) => [
            'name'   => $d->name,
            'lat'    => (float) $d->latitude,
            'lng'    => (float) $d->longitude,
            'active' => $d->status === 'active',
            'lahan'  => $d->lahan?->kode_lahan ?? '—',
            'petani' => $d->lahan?->petani?->nama ?? '—',
        ]);

        $totalPengepul = 0;
        try {
            $totalPengepul = Pengepul::count();
        } catch (\Throwable $e) {}

        return [
            'lahans'      => $lahans,
            'lahanGeo'    => $lahanGeo,
            'pengepulGeo' => $pengepulGeo,
            'deviceGeo'   => $deviceGeo,
            'statistik'   => [
                'total_petani'   => Petani::where('aktif', true)->count(),
                'total_lahan'    => $lahans->count(),
                'total_pohon'    => (int) $lahans->sum('pohon_di_deres'),
                'total_pengepul' => $totalPengepul,
                'total_batch'    => BatchProduksi::count(),
                'total_device'   => Device::count(),
            ],
        ];
    }
}
