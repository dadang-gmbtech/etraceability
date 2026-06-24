<?php

namespace App\Livewire;

use App\Models\SetoranGula;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PetaniDashboard extends Component
{
    public bool $belumDikonfigurasi = false;

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isPetani()) {
            redirect()->route('filament.admin.auth.login');
            return;
        }
        if (!$user->petani_id) {
            $this->belumDikonfigurasi = true;
        }
    }

    public function render()
    {
        if ($this->belumDikonfigurasi) {
            return view('livewire.petani-dashboard', [
                'totalKg'         => 0,
                'totalUang'       => 0,
                'jumlahSetoran'   => 0,
                'rekapBulanan'    => collect(),
                'setoranTerakhir' => collect(),
                'belumDikonfigurasi' => true,
            ])->layout('components.layouts.app');
        }

        $petaniId = auth()->user()->petani_id;

        $totalKg     = SetoranGula::where('petani_id', $petaniId)->sum('berat_kg');
        $totalUang   = SetoranGula::where('petani_id', $petaniId)->sum('total_harga');
        $jumlahSetoran = SetoranGula::where('petani_id', $petaniId)->count();

        $rekapBulanan = SetoranGula::where('petani_id', $petaniId)
            ->select(
                DB::raw("TO_CHAR(tanggal_setor, 'YYYY-MM') as bulan"),
                DB::raw("COUNT(*) as jumlah_setor"),
                DB::raw("SUM(berat_kg) as total_kg"),
                DB::raw("SUM(total_harga) as total_uang")
            )
            ->groupBy('bulan')
            ->orderByDesc('bulan')
            ->limit(12)
            ->get();

        $setoranTerakhir = SetoranGula::where('petani_id', $petaniId)
            ->with('batchProduksi')
            ->orderByDesc('tanggal_setor')
            ->limit(10)
            ->get();

        return view('livewire.petani-dashboard', compact(
            'totalKg', 'totalUang', 'jumlahSetoran', 'rekapBulanan', 'setoranTerakhir'
        ))->layout('components.layouts.app');
    }
}
