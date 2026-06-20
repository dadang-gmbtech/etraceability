<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RuteDistribusi;
use App\Models\BatchProduksi;
use App\Models\Distributor;

class RuteDistribusiManager extends Component
{
    use WithPagination;

    public $showForm = false;
    public $isEdit = false;
    public $ruteId = null;

    public $batch_produksi_id;
    public $distributor_id;
    public $asal;
    public $tujuan;
    public $waktu_berangkat;
    public $waktu_tiba;
    public $koordinat_jalur = []; // Array of [lat, lng]

    public function render()
    {
        $ruteList = RuteDistribusi::with(['batchProduksi', 'distributor'])->latest()->paginate(10);
        $batchOptions = BatchProduksi::latest()->get();
        $distributorOptions = Distributor::all();

        return view('livewire.rute-distribusi-manager', compact('ruteList', 'batchOptions', 'distributorOptions'))
               ->layout('components.layouts.app');
    }

    public function bukaForm()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function batal()
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'ruteId', 'batch_produksi_id', 'distributor_id', 'asal', 'tujuan',
            'waktu_berangkat', 'waktu_tiba', 'koordinat_jalur', 'isEdit'
        ]);
        $this->dispatch('resetMap');
    }

    public function edit($id)
    {
        $rute = RuteDistribusi::findOrFail($id);
        $this->ruteId = $rute->id;
        $this->batch_produksi_id = $rute->batch_produksi_id;
        $this->distributor_id = $rute->distributor_id;
        $this->asal = $rute->asal;
        $this->tujuan = $rute->tujuan;
        $this->waktu_berangkat = $rute->waktu_berangkat ? $rute->waktu_berangkat->format('Y-m-d\TH:i') : null;
        $this->waktu_tiba = $rute->waktu_tiba ? $rute->waktu_tiba->format('Y-m-d\TH:i') : null;
        $this->koordinat_jalur = $rute->jalur_coordinates;
        $this->isEdit = true;
        $this->showForm = true;

        $this->dispatch('loadRoute', koordinat: $this->koordinat_jalur);
    }

    public function simpan()
    {
        $this->validate([
            'batch_produksi_id' => 'required',
            'distributor_id' => 'required',
            'asal' => 'required|string|max:255',
            'tujuan' => 'required|string|max:255',
            'koordinat_jalur' => 'required|array|min:2', // minimal 2 titik untuk garis
        ]);

        if ($this->isEdit) {
            $rute = RuteDistribusi::findOrFail($this->ruteId);
        } else {
            $rute = new RuteDistribusi();
        }

        $rute->batch_produksi_id = $this->batch_produksi_id;
        $rute->distributor_id = $this->distributor_id;
        $rute->asal = $this->asal;
        $rute->tujuan = $this->tujuan;
        $rute->waktu_berangkat = $this->waktu_berangkat ?: null;
        $rute->waktu_tiba = $this->waktu_tiba ?: null;
        $rute->save();

        // Simpan koordinat LineString
        if (!empty($this->koordinat_jalur)) {
            $rute->setJalur($this->koordinat_jalur);
        }

        session()->flash('sukses', 'Rute Distribusi berhasil ' . ($this->isEdit ? 'diperbarui!' : 'disimpan!'));
        $this->batal();
    }

    public function hapus($id)
    {
        RuteDistribusi::findOrFail($id)->delete();
        session()->flash('sukses', 'Rute Distribusi berhasil dihapus!');
    }
}
