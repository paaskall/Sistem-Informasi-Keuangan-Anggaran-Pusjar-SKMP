<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'target_id', 'program_id', 'sub_kegiatan_id',
        'komponen_belanja_id', 'nominal',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function target()
    {
        return $this->belongsTo(Target::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function subKegiatan()
    {
        return $this->belongsTo(SubKegiatan::class);
    }

    public function komponenBelanja()
    {
        return $this->belongsTo(KomponenBelanja::class);
    }
}