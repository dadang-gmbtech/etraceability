<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'lahan_id',
        'name',
        'latitude',
        'longitude',
        'status',
    ];

    public function lahan()
    {
        return $this->belongsTo(Lahan::class);
    }

    public function soilMeasurements()
    {
        return $this->hasMany(SoilMeasurement::class);
    }
}
