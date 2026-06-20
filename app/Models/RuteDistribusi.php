<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuteDistribusi extends Model
{
    use HasFactory;

    protected $table = 'rute_distribusi';

    protected $fillable = [
        'batch_produksi_id', 'distributor_id', 'asal', 'tujuan',
        'waktu_berangkat', 'waktu_tiba',
    ];

    protected $casts = [
        'waktu_berangkat' => 'datetime',
        'waktu_tiba' => 'datetime',
    ];

    public function batchProduksi()
    {
        return $this->belongsTo(BatchProduksi::class);
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    /**
     * Ambil jalur rute sebagai array koordinat [[lat, lng], ...]
     * siap dipakai langsung sebagai polyline di Leaflet.
     */
    public function getJalurCoordinatesAttribute(): array
    {
        if (! $this->exists) {
            return [];
        }

        $result = \DB::selectOne(
            'SELECT ST_AsGeoJSON(jalur) as geojson FROM rute_distribusi WHERE id = ?',
            [$this->id]
        );

        if (! $result || ! $result->geojson) {
            return [];
        }

        $geojson = json_decode($result->geojson, true);

        // GeoJSON pakai urutan [lng, lat], Leaflet butuh [lat, lng] → dibalik
        return array_map(fn ($coord) => [$coord[1], $coord[0]], $geojson['coordinates'] ?? []);
    }

    /**
     * Set jalur rute dari array koordinat [[lat, lng], [lat, lng], ...]
     */
    public function setJalur(array $coordinates): void
    {
        // Bangun string WKT LINESTRING dari array koordinat
        $points = array_map(fn ($c) => "{$c[1]} {$c[0]}", $coordinates); // WKT pakai lng lat
        $wkt = 'LINESTRING(' . implode(', ', $points) . ')';

        \DB::statement(
            'UPDATE rute_distribusi SET jalur = ST_SetSRID(ST_GeomFromText(?), 4326) WHERE id = ?',
            [$wkt, $this->id]
        );
    }
}
