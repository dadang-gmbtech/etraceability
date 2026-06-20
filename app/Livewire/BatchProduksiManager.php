<?php

namespace App\Livewire;

use App\Models\BatchProduksi;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class BatchProduksiManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $batchList = BatchProduksi::query()
            ->with(['pengepul', 'setoranGulas.petani'])
            ->when($this->search, fn ($q) => $q->where('trace_id', 'ilike', "%{$this->search}%"))
            ->when($this->filterStatus, fn ($q) => $q->where('status_batch', $this->filterStatus))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.batch-produksi-manager', [
            'batchList' => $batchList,
        ]);
    }
}
