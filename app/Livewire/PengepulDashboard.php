<?php

namespace App\Livewire;

use App\Models\SetoranGula;
use App\Models\Petani;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PengepulDashboard extends Component
{
    public bool $belumDikonfigurasi = false;

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isPengepul()) {
            redirect()->route('filament.admin.auth.login');
            return;
        }
        if (!$user->pengepul_id) {
            $this->belumDikonfigurasi = true;
        }
    }

    public function render()
    {
        if ($this->belumDikonfigurasi) {
            return view('livewire.pengepul-dashboard', [
                'totalKg' => 0, 'totalUang' => 0, 'jumlahPetani' => 0,
                'rekapPerPetani' => collect(), 'rekapBulanan' => collect(),
                'belumDikonfigurasi' => true,
            ])->layout('components.layouts.app');
        }

        $pengepulId = auth()->user()->pengepul_id;

        $totalKg = SetoranGula::where('pengepul_id', $pengepulId)->sum('berat_kg');
        $totalUang = SetoranGula::where('pengepul_id', $pengepulId)->sum('total_harga');
        $jumlahPetani = SetoranGula::where('pengepul_id', $pengepulId)
            ->distinct('petani_id')->count('petani_id');

        $rekapPerPetani = SetoranGula::where('pengepul_id', $pengepulId)
            ->select(
                'petani_id',
                DB::raw("SUM(berat_kg) as total_kg"),
                DB::raw("SUM(total_harga) as total_uang"),
                DB::raw("COUNT(*) as jumlah_setor"),
                DB::raw("MAX(tanggal_setor) as setor_terakhir")
            )
            ->with('petani')
            ->groupBy('petani_id')
            ->orderByDesc('total_kg')
            ->get();

        $rekapBulanan = SetoranGula::where('pengepul_id', $pengepulId)
            ->select(
                DB::raw("TO_CHAR(tanggal_setor, 'YYYY-MM') as bulan"),
                DB::raw("SUM(berat_kg) as total_kg"),
                DB::raw("COUNT(DISTINCT petani_id) as jumlah_petani")
            )
            ->groupBy('bulan')
            ->orderByDesc('bulan')
            ->limit(12)
            ->get();

        return view('livewire.pengepul-dashboard', compact(
            'totalKg', 'totalUang', 'jumlahPetani', 'rekapPerPetani', 'rekapBulanan'
        ))->layout('components.layouts.app');
    }
}
