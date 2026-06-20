<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchProduksi extends Model
{
    use HasFactory;

    protected $table = 'batch_produksi';

    protected $fillable = [
        'trace_id', 'pengepul_id', 'tanggal_pengumpulan', 
        'status_batch', 'is_organik', 'berat_total_kg'
    ];

    protected $casts = [
        'tanggal_pengumpulan' => 'date',
        'is_organik' => 'boolean'
    ];

    public function pengepul()
    {
        return $this->belongsTo(Pengepul::class);
    }

    public function setoranGulas()
    {
        return $this->hasMany(SetoranGula::class);
    }

    public function events()
    {
        return $this->hasMany(EventTraceability::class);
    }

    // Generate Trace ID
    public static function generateTraceId()
    {
        $prefix = 'GKO'; // Gula Kelapa Organik
        $date = date('Ymd');
        
        $lastBatch = self::where('trace_id', 'like', "{$prefix}-{$date}-%")->orderBy('id', 'desc')->lockForUpdate()->first();
        $sequence = 1;

        if ($lastBatch) {
            $parts = explode('-', $lastBatch->trace_id);
            $sequence = intval(end($parts)) + 1;
        }

        return sprintf("%s-%s-%04d", $prefix, $date, $sequence);
    }
}
