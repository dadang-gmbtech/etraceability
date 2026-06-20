<?php

namespace App\Livewire;

use App\Models\BatchProduksi;
use App\Models\Device;
use App\Models\Lahan;
use App\Models\Petani;
use App\Models\Pengepul;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PetaSebaran extends Component
{
    public function render()
    {
        $lahans   = Lahan::with('petani')->get();
        $pengepul = Pengepul::all();
        $devices  = Device::with(['lahan.petani', 'soilMeasurements' => fn ($q) => $q->latest()->limit(1)])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('livewire.peta-sebaran', [
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
        ]);
    }
}
