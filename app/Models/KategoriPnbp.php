<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriPnbp extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kategori_pnpbs';   // ⭐ FIX: paksa nama tabel

    protected $fillable = ['kode', 'nama'];

    public function details()
    {
        return $this->hasMany(PnbpDetail::class, 'kategori_pnbp_id');
    }
}
