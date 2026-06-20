<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HargaHarian extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'jenis_produk',
        'harga_per_kg',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}
