<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SertifikasiOrganik extends Model
{
    use HasFactory;

    protected $table = 'sertifikasi_organik';

    protected $fillable = [
        'petani_id', 'nomor_sertifikat', 'lembaga_sertifikasi',
        'tanggal_terbit', 'tanggal_kadaluarsa', 'status', 'file_dokumen',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_kadaluarsa' => 'date',
    ];

    public function petani()
    {
        return $this->belongsTo(Petani::class);
    }

    public function getIsValidAttribute(): bool
    {
        return $this->status === 'aktif' && $this->tanggal_kadaluarsa->isFuture();
    }
}
