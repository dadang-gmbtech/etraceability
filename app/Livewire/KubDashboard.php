<?php

namespace App\Livewire;

use App\Models\SetoranGula;
use App\Models\Pengepul;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class KubDashboard extends Component
{
    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isKub()) {
            abort(403);
        }
    }

    public function render()
    {
        $totalKg = SetoranGula::sum('berat_kg');
        $totalUang = SetoranGula::sum('total_harga');
        $jumlahPetani = SetoranGula::distinct('petani_id')->count('petani_id');
        $jumlahPengepul = SetoranGula::distinct('pengepul_id')->count('pengepul_id');

        $rekapPerPengepul = SetoranGula::select(
                'pengepul_id',
                DB::raw("SUM(berat_kg) as total_kg"),
                DB::raw("SUM(total_harga) as total_uang"),
                DB::raw("COUNT(DISTINCT petani_id) as jumlah_petani"),
                DB::raw("COUNT(*) as jumlah_setor")
            )
            ->with('pengepul')
            ->groupBy('pengepul_id')
            ->orderByDesc('total_kg')
            ->get();

        $rekapBulanan = SetoranGula::select(
                DB::raw("TO_CHAR(tanggal_setor, 'YYYY-MM') as bulan"),
                DB::raw("SUM(berat_kg) as total_kg"),
                DB::raw("SUM(total_harga) as total_uang"),
                DB::raw("COUNT(DISTINCT petani_id) as jumlah_petani")
            )
            ->groupBy('bulan')
            ->orderByDesc('bulan')
            ->limit(12)
            ->get();

        return view('livewire.kub-dashboard', compact(
            'totalKg', 'totalUang', 'jumlahPetani', 'jumlahPengepul', 'rekapPerPengepul', 'rekapBulanan'
        ))->layout('components.layouts.app');
    }
}
