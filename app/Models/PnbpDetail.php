<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PnbpDetail extends Model
{
    use HasFactory;

    protected $table = 'pnbp_details';

    protected $fillable = [
        'pnbp_id', 'kategori_pnbp_id', 'parent_id', 'bulan',
        'target', 'realisasi', 'peserta_target', 'peserta_realisasi', 'keterangan',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'target' => 'decimal:2',
        'realisasi' => 'decimal:2',
        'peserta_target' => 'integer',
        'peserta_realisasi' => 'integer',
    ];

    public function pnbp()
    {
        return $this->belongsTo(Pnbp::class);
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriPnbp::class, 'kategori_pnbp_id');
    }

    public function parent()
    {
        return $this->belongsTo(PnbpDetail::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PnbpDetail::class, 'parent_id');
    }

    public function getBulanNamaAttribute(): string
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ][$this->bulan] ?? '-';
    }

    public function getCapaianAttribute(): int
    {
        if ($this->target <= 0) return 0;
        return (int) round(($this->realisasi / $this->target) * 100);
    }
}
