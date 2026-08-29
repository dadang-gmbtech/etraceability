<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatasWilayah extends Model
{
    protected $fillable = ['jenis', 'nama', 'kode', 'koordinat'];

    protected $casts = ['koordinat' => 'array'];
}
