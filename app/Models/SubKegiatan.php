<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubKegiatan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['program_id', 'kode', 'nama'];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function targetDetails()
    {
        return $this->hasMany(TargetDetail::class);
    }

    public function realisasiDetails()
    {
        return $this->hasMany(RealisasiDetail::class);
    }
}