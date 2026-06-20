<?php

namespace App\Livewire;

use App\Models\Petani;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PetaniManager extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $petaniList = Petani::query()
            ->withCount('lahans')
            ->when($this->search, fn ($q) => $q
                ->where('nama', 'ilike', "%{$this->search}%")
                ->orWhere('kode_petani', 'ilike', "%{$this->search}%")
                ->orWhere('desa', 'ilike', "%{$this->search}%"))
            ->orderBy('nama')
            ->paginate(15);

        return view('livewire.petani-manager', [
            'petaniList' => $petaniList,
        ]);
    }
}
