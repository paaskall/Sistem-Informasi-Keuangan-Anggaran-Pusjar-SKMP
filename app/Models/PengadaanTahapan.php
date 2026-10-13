<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengadaanTahapan extends Model
{
    use HasFactory;

    protected $fillable = [
        'pengadaan_id',
        'nama',
        'urutan',
        'selesai',
        'tanggal_selesai',
        'kendala',
        'dampak',
        'tindak_lanjut',
        'kendala_selesai',        
        'kendala_selesai_at',     
    ];

    protected $casts = [
        'selesai' => 'boolean',
        'urutan' => 'integer',
        'tanggal_selesai' => 'date',
        'kendala_selesai' => 'boolean',           
        'kendala_selesai_at' => 'date',           
    ];

    public function pengadaan()
    {
        return $this->belongsTo(Pengadaan::class);
    }
}