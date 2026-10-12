<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KomponenBelanja extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['kode', 'nama'];

    public function targetDetails()
    {
        return $this->hasMany(TargetDetail::class);
    }

    public function realisasiDetails()
    {
        return $this->hasMany(RealisasiDetail::class);
    }

    public function kendalas()
    {
        return $this->hasMany(Kendala::class);
    }
}