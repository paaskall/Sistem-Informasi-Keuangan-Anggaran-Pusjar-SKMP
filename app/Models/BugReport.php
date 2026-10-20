<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BugReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'judul', 'deskripsi', 'kategori', 'prioritas',
        'status', 'halaman_terkait', 'catatan_admin',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getKategoriLabelAttribute(): string
    {
        return match ($this->kategori) {
            'ui_ux' => 'UI/UX',
            'fungsional' => 'Fungsional',
            'performa' => 'Performa',
            'data' => 'Data',
            default => 'Lainnya',
        };
    }

    public function getPrioritasLabelAttribute(): string
    {
        return match ($this->prioritas) {
            'rendah' => 'Rendah',
            'sedang' => 'Sedang',
            'tinggi' => 'Tinggi',
            'kritis' => 'Kritis',
            default => '-',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'baru' => 'Baru',
            'diproses' => 'Diproses',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            default => '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'baru' => 'blue',
            'diproses' => 'amber',
            'selesai' => 'green',
            'ditolak' => 'red',
            default => 'gray',
        };
    }

    public function getPrioritasColorAttribute(): string
    {
        return match ($this->prioritas) {
            'kritis' => 'red',
            'tinggi' => 'orange',
            'sedang' => 'amber',
            'rendah' => 'blue',
            default => 'gray',
        };
    }
}