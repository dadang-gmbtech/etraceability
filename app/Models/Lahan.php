<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lahan extends Model
{
    use HasFactory;

    protected $fillable = [
        'petani_id',
        'kode_lahan',
        'pemilik',
        'blok_lahan',
        'desa',
        'jenis_geometri',
        'koordinat',
        'luas_lahan',
        'jumlah_nira',
        'jumlah_kelapa',
    ];

    protected $casts = [
        'koordinat' => 'array',
    ];

    public function petani()
    {
        return $this->belongsTo(Petani::class);
    }
    
    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
