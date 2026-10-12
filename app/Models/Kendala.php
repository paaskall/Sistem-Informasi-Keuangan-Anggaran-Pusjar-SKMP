<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kendala extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'tanggal',
        'periode',
        'realisasi_id',
        'target_id',
        'komponen_belanja_id',
        'kategori_kendala_id',
        'deskripsi',
        'dampak',
        'tindak_lanjut',
        'status',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'periode' => 'integer',
    ];

    // ============ RELATIONSHIPS ============
    public function kategoriKendala()
    {
        return $this->belongsTo(KategoriKendala::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function realisasi()
    {
        return $this->belongsTo(Realisasi::class);
    }

    public function target()
    {
        return $this->belongsTo(Target::class);
    }

    public function komponenBelanja()
    {
        return $this->belongsTo(KomponenBelanja::class);
    }

    // ============ SCOPES ============
    public function scopeProses($query)
    {
        return $query->where('status', 'Proses');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'Selesai');
    }
}
