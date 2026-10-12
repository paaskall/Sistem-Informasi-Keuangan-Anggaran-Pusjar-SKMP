<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriKendala extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nama'];

    public function kendalas()
    {
        return $this->hasMany(Kendala::class);
    }
}