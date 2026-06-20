<?php

namespace App\Livewire;

use App\Models\BatchProduksi;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class TracePublic extends Component
{
    public string $trace_id;
    public ?BatchProduksi $batch = null;
    public bool $notFound = false;

    public function mount(string $trace_id)
    {
        $this->trace_id = $trace_id;

        $this->batch = BatchProduksi::with([
            'pengepul',
            'setoranGulas.petani.lahans',
            'events',
        ])->where('trace_id', $trace_id)->first();

        $this->notFound = is_null($this->batch);
    }

    public function render()
    {
        return view('livewire.trace-public');
    }
}
