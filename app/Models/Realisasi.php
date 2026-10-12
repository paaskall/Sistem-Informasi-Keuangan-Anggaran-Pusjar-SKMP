<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Realisasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'realisasis';

    protected $fillable = [
        'target_id', 'tahun', 'bulan', 'tanggal',
        'nominal', 'keterangan', 'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tahun' => 'integer',
        'bulan' => 'integer',
        'nominal' => 'decimal:2',
    ];

    public function target()
    {
        return $this->belongsTo(Target::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(RealisasiDetail::class);
    }

    public function kendalas()
    {
        return $this->hasMany(Kendala::class);
    }

    // ============ ACCESSORS ============
    public function getBulanNamaAttribute(): string
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $bulan[$this->bulan] ?? '-';
    }

    // ============ SCOPES ============
    public function scopeTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }
}