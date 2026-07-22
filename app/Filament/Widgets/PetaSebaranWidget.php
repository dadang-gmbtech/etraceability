<?php

namespace App\Filament\Widgets;

use App\Models\BatchProduksi;
use App\Models\Device;
use App\Models\Lahan;
use App\Models\Petani;
use App\Models\Pengepul;
use Filament\Widgets\Widget;

class PetaSebaranWidget extends Widget
{
    protected string $view = 'filament.widgets.peta-sebaran-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1;

    protected function getViewData(): array
    {
        $lahans   = Lahan::with('petani')->get();
        $pengepul = Pengepul::all();
        $devices  = Device::with(['lahan.petani', 'soilMeasurements' => fn ($q) => $q->latest()->limit(1)])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return [
            'lahans'    => $lahans,
            'pengepul'  => $pengepul,
            'devices'   => $devices,
            'statistik' => [
                'total_petani'   => Petani::where('aktif', true)->count(),
                'total_lahan'    => $lahans->count(),
                'total_pohon'    => $lahans->sum('jumlah_pohon'),
                'total_pengepul' => $pengepul->count(),
                'total_batch'    => BatchProduksi::count(),
                'total_device'   => Device::count(),
            ],
        ];
    }
}
