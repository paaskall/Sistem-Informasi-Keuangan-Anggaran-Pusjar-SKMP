<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pnbp extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'pnbps';

    protected $fillable = ['tahun', 'unit'];

    protected $casts = [
        'tahun' => 'integer',
    ];

    // Cache internal
    private ?float $_cachedTotalTarget = null;
    private ?float $_cachedTotalRealisasi = null;

    public function details()
    {
        return $this->hasMany(PnbpDetail::class);
    }

    // ============ ACCESSORS (dengan cache) ============

    public function getTotalTargetAttribute(): float
    {
        if ($this->_cachedTotalTarget !== null) {
            return $this->_cachedTotalTarget;
        }

        if ($this->relationLoaded('details')) {
            $sum = (float) $this->details->sum('target');
        } else {
            $sum = (float) $this->details()->sum('target');
        }

        return $this->_cachedTotalTarget = $sum;
    }

    public function getTotalRealisasiAttribute(): float
    {
        if ($this->_cachedTotalRealisasi !== null) {
            return $this->_cachedTotalRealisasi;
        }

        if ($this->relationLoaded('details')) {
            $sum = (float) $this->details->sum('realisasi');
        } else {
            $sum = (float) $this->details()->sum('realisasi');
        }

        return $this->_cachedTotalRealisasi = $sum;
    }

    public function getCapaianAttribute(): int
    {
        $target = $this->total_target;
        if ($target <= 0) return 0;
        return (int) round(($this->total_realisasi / $target) * 100);
    }

    // ============ SCOPES ============

    public function scopeTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }
}