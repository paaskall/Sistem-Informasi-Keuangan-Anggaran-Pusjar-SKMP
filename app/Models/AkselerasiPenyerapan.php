<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AkselerasiPenyerapan extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'kegiatan',
        'jangka_waktu',
        'progress',
        'keterangan',
        'urutan',
        'user_id',
    ];

    protected $casts = [
        'progress' => 'integer',
        'urutan' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: hanya yang progress < 100 (masih berjalan)
     */
    public function scopeAktif($query)
    {
        return $query->where('progress', '<', 100);
    }

    /**
     * Scope: urut berdasarkan urutan
     */
    public function scopeTerurut($query)
    {
        return $query->orderBy('urutan', 'asc')->orderBy('id', 'asc');
    }
}