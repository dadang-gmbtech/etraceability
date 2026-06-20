<?php

namespace App\Livewire;

use App\Models\BatchProduksi;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class BatchTraceabilityView extends Component
{
    public string $traceId;
    public BatchProduksi $batch;

    public function mount(string $trace_id): void
    {
        $this->traceId = $trace_id;
        $this->batch = BatchProduksi::with([
            'setoranGulas.petani.lahans',
            'pengepul',
        ])->where('trace_id', $trace_id)->firstOrFail();
    }

    public function render()
    {
        $setorans   = $this->batch->setoranGulas;
        $petaniList = $setorans->groupBy('petani_id');

        $allLahans = collect();
        foreach ($petaniList as $group) {
            $petani = $group->first()?->petani;
            if ($petani) {
                $allLahans = $allLahans->merge($petani->lahans);
            }
        }

        return view('livewire.batch-traceability-view', [
            'batch'      => $this->batch,
            'petaniList' => $petaniList,
            'allLahans'  => $allLahans,
        ]);
    }
}
