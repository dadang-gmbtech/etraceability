<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoilMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'ph_level',
        'moisture',
        'nitrogen',
        'phosphorus',
        'potassium',
        'temperature',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
