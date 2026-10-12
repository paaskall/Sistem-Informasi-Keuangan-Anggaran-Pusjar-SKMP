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

    // ============ RELATIONSHIPS ============
    public function details()
    {
        return $this->hasMany(PnbpDetail::class);
    }

    // ============ ACCESSORS ============
    public function getTotalTargetAttribute(): float
    {
        return (float) $this->details->sum('target');
    }

    public function getTotalRealisasiAttribute(): float
    {
        return (float) $this->details->sum('realisasi');
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
