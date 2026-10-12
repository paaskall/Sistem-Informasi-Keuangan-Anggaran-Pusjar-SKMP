<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengadaan extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'kode', 'nama', 'jenis_pengadaan_id', 'status_id', 'progress',
        'nilai', 'penyedia', 'tanggal_mulai', 'tanggal_selesai',
    ];

    protected $casts = [
        'progress' => 'integer',
        'nilai' => 'decimal:2',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    // ============ RELATIONSHIPS ============
    public function jenisPengadaan()
    {
        return $this->belongsTo(JenisPengadaan::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function tahapans()
    {
        return $this->hasMany(PengadaanTahapan::class)->orderBy('urutan');
    }

    // ============ ACCESSORS ============
    public function getJumlahTahapanAttribute(): int
    {
        return $this->tahapans->count();
    }

    public function getJumlahSelesaiAttribute(): int
    {
        return $this->tahapans->where('selesai', true)->count();
    }

    /**
     * Progress otomatis dari tahapan.
     */
    public function getProgressOtomatisAttribute(): int
    {
        $total = $this->jumlah_tahapan;
        if ($total === 0) return 0;

        return (int) round(($this->jumlah_selesai / $total) * 100);
    }

    /**
     * Recalculate & update progress + status ke database.
     */
    public function recalculate(): void
    {
        $progress = $this->progress_otomatis;

        // Auto-set status berdasarkan progress
        $statusId = null;
        if ($progress === 0) {
            $statusId = Status::where('nama', 'Draft')->value('id');
        } elseif ($progress >= 100) {
            $statusId = Status::where('nama', 'Selesai')->value('id');
        } else {
            $statusId = Status::where('nama', 'Proses')->value('id');
        }

        $this->update([
            'progress' => $progress,
            'status_id' => $statusId,
        ]);
    }
}