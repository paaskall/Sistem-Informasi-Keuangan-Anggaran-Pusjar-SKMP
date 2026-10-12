<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengadaanTahapan extends Model
{
    use HasFactory;

    protected $fillable = [
        'pengadaan_id', 'nama', 'urutan', 'selesai',
        'tanggal_selesai', 'kendala',
    ];

    protected $casts = [
        'selesai' => 'boolean',
        'urutan' => 'integer',
        'tanggal_selesai' => 'date',
    ];

    // ============ RELATIONSHIPS ============
    public function pengadaan()
    {
        return $this->belongsTo(Pengadaan::class);
    }
}
