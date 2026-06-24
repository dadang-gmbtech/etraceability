<?php

namespace App\Livewire;

use App\Models\SetoranGula;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PetaniDashboard extends Component
{
    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isPetani() || !$user->petani_id) {
            abort(403);
        }
    }

    public function render()
    {
        $petaniId = auth()->user()->petani_id;

        $totalKg = SetoranGula::where('petani_id', $petaniId)->sum('berat_kg');
        $totalUang = SetoranGula::where('petani_id', $petaniId)->sum('total_harga');

        $rekapBulanan = SetoranGula::where('petani_id', $petaniId)
            ->select(
                DB::raw("TO_CHAR(tanggal_setor, 'YYYY-MM') as bulan"),
                DB::raw("SUM(berat_kg) as total_kg"),
                DB::raw("SUM(total_harga) as total_uang"),
                DB::raw("COUNT(*) as jumlah_setor")
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
            'totalKg', 'totalUang', 'rekapBulanan', 'setoranTerakhir'
        ))->layout('components.layouts.app');
    }
}
