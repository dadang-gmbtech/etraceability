<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventTraceability extends Model
{
    use HasFactory;

    protected $table = 'event_traceability';

    protected $fillable = [
        'batch_produksi_id', 'tipe_event', 'aktor_tipe', 'aktor_id',
        'lokasi_nama', 'lokasi_lat', 'lokasi_lng', 'catatan', 'waktu_kejadian',
    ];

    protected $casts = [
        'waktu_kejadian' => 'datetime',
        'lokasi_lat' => 'float',
        'lokasi_lng' => 'float',
    ];

    public function batchProduksi()
    {
        return $this->belongsTo(BatchProduksi::class);
    }

    /**
     * Resolve model aktor terkait secara dinamis (polymorphic manual)
     * berdasarkan kolom aktor_tipe + aktor_id.
     */
    public function getAktorAttribute()
    {
        return match ($this->aktor_tipe) {
            'petani'    => Petani::find($this->aktor_id),
            'pengepul'  => Pengepul::find($this->aktor_id),
            default     => null,
        };
    }
}
