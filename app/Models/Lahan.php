<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lahan extends Model
{
    use HasFactory;

    protected $fillable = [
        'petani_id',
        'nama_lahan',
        'pemilik',
        'jumlah_pohon',
        'jenis_geometri',
        'koordinat',
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
