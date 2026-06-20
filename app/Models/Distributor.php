<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distributor extends Model
{
    use HasFactory;

    protected $table = 'distributor';

    protected $fillable = ['kode_distributor', 'nama_perusahaan', 'no_hp', 'alamat'];

    protected $appends = ['lokasi_lat', 'lokasi_lng'];

    // FIX N+1: cache koordinat per-instance
    protected ?array $_coordCache = null;

    public function getLokasiLatAttribute()
    {
        return $this->getCoordinate('lat');
    }

    public function getLokasiLngAttribute()
    {
        return $this->getCoordinate('lng');
    }

    protected function getCoordinate(string $type)
    {
        if (! $this->exists) {
            return null;
        }

        if ($this->_coordCache === null) {
            $result = \DB::selectOne(
                'SELECT ST_Y(lokasi_gudang) as lat, ST_X(lokasi_gudang) as lng FROM distributor WHERE id = ?',
                [$this->id]
            );
            $this->_coordCache = $result
                ? ['lat' => $result->lat, 'lng' => $result->lng]
                : ['lat' => null, 'lng' => null];
        }

        return $this->_coordCache[$type] ?? null;
    }

    public function setLokasi(float $lat, float $lng): void
    {
        \DB::statement(
            'UPDATE distributor SET lokasi_gudang = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?',
            [$lng, $lat, $this->id]
        );
    }

    public function ruteDistribusi()
    {
        return $this->hasMany(RuteDistribusi::class);
    }
}
