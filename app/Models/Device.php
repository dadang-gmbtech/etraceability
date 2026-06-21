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
        'api_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $device) {
            if (empty($device->api_token)) {
                $device->api_token = \Illuminate\Support\Str::random(48);
            }
        });
    }

    public function lahan()
    {
        return $this->belongsTo(Lahan::class);
    }

    public function soilMeasurements()
    {
        return $this->hasMany(SoilMeasurement::class);
    }
}
