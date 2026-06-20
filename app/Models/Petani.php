<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Petani extends Model
{
    use HasFactory;

    protected $table = 'petani';

    protected $fillable = [
        'kode_petani', 'nama', 'no_hp', 'alamat', 'desa', 'kecamatan',
        'kabupaten', 'aktif',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($petani) {
            if (empty($petani->kode_petani)) {
                $petani->kode_petani = static::generateKodePetani();
            }
        });
    }

    public static function generateKodePetani(): string
    {
        $last = static::orderBy('id', 'desc')->first();
        $seq = 1;

        if ($last && preg_match('/PTN-(\d+)$/', $last->kode_petani, $m)) {
            $seq = intval($m[1]) + 1;
        }

        return 'PTN-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function lahans()
    {
        return $this->hasMany(Lahan::class);
    }

    public function setoranGulas()
    {
        return $this->hasMany(SetoranGula::class);
    }

    public function getTotalPohonAttribute(): int
    {
        return $this->lahans()->sum('jumlah_pohon');
    }
}
