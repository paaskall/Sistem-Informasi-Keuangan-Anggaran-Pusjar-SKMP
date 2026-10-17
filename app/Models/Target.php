<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Target extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['tahun', 'bulan', 'unit'];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
    ];

    // Cache internal (bukan DB) — biar akses berulang tidak query ulang
    private ?float $_cachedTotalNominal = null;
    private ?float $_cachedTotalRealisasi = null;

    public function details()
    {
        return $this->hasMany(TargetDetail::class);
    }

    public function realisasis()
    {
        return $this->hasMany(Realisasi::class);
    }

    public function kendalas()
    {
        return $this->hasMany(Kendala::class);
    }

    // ============ ACCESSORS (dengan cache) ============

    public function getTotalNominalAttribute(): float
    {
        if ($this->_cachedTotalNominal !== null) {
            return $this->_cachedTotalNominal;
        }

        if ($this->relationLoaded('details')) {
            $sum = (float) $this->details->sum('nominal');
        } else {
            $sum = (float) $this->details()->sum('nominal');
        }

        return $this->_cachedTotalNominal = $sum;
    }

    public function getTotalRealisasiAttribute(): float
    {
        if ($this->_cachedTotalRealisasi !== null) {
            return $this->_cachedTotalRealisasi;
        }

        if ($this->relationLoaded('realisasis')) {
            $sum = (float) $this->realisasis->sum('nominal');
        } else {
            $sum = (float) $this->realisasis()->sum('nominal');
        }

        return $this->_cachedTotalRealisasi = $sum;
    }

    public function getCapaianAttribute(): int
    {
        $target = $this->total_nominal;
        if ($target <= 0) return 0;
        return (int) round(($this->total_realisasi / $target) * 100);
    }

    public function getBulanNamaAttribute(): string
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ][$this->bulan] ?? '-';
    }

    public function getBulanShortAttribute(): string
    {
        $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        return $bulan[$this->bulan - 1] ?? '-';
    }

    public function scopeTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('bulan');
    }
}