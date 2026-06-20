<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetoranGula extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_produksi_id', 'petani_id', 'pengepul_id', 'jenis_produk', 
        'berat_kg', 'tanggal_setor', 'hari_akumulasi', 'is_anomali', 
        'keterangan_anomali', 'total_harga'
    ];

    protected $casts = [
        'tanggal_setor' => 'date',
        'is_anomali' => 'boolean'
    ];

    public function batchProduksi()
    {
        return $this->belongsTo(BatchProduksi::class);
    }

    public function petani()
    {
        return $this->belongsTo(Petani::class);
    }

    public function pengepul()
    {
        return $this->belongsTo(Pengepul::class);
    }
}
