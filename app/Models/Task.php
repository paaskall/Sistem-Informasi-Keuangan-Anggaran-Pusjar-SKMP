<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Task extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'judul',
        'deskripsi',
        'bulan',
        'tahun',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'assigned_to',
        'created_by',
        'lampiran',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    // ============ RELATIONSHIPS ============

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ============ SCOPES ============

    public function scopeAktif($query)
    {
        return $query->whereNotIn('status', ['selesai', 'dibatalkan']);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBulanIni($query)
    {
        return $query->where('bulan', (int) date('n'))
                     ->where('tahun', (int) date('Y'));
    }

    public function scopeByMonth($query, int $bulan, int $tahun)
    {
        return $query->where('bulan', $bulan)->where('tahun', $tahun);
    }

    /**
     * Task yang belum selesai untuk widget "Upcoming Tasks".
     *
     * Urutan:
     * 1. Overdue (tanggal_selesai < hari ini) — paling urgent
     * 2. Hari ini (tanggal_selesai = hari ini)
     * 3. Akan datang (tanggal_selesai > hari ini)
     * 4. Tanpa tanggal selesai (pakai tanggal_mulai sebagai fallback)
     */
    public function scopeUpcoming($query, int $limit = 5)
    {
        $today = now()->startOfDay();

        return $query->aktif()
            ->orderByRaw("
                CASE
                    WHEN tanggal_selesai IS NOT NULL AND tanggal_selesai < ? THEN 1
                    WHEN tanggal_selesai = ? THEN 2
                    WHEN tanggal_selesai > ? THEN 3
                    ELSE 4
                END
            ", [$today, $today, $today])
            ->orderByRaw('COALESCE(tanggal_selesai, tanggal_mulai) ASC')
            ->limit($limit);
    }

    /**
     * Task yang overdue (lewat tanggal selesai tapi belum selesai).
     */
    public function scopeOverdue($query)
    {
        return $query->aktif()
            ->whereNotNull('tanggal_selesai')
            ->where('tanggal_selesai', '<', now()->startOfDay());
    }

    // ============ ACCESSORS ============

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'belum' => 'Belum Dikerjakan',
            'proses' => 'Sedang Proses',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'belum' => 'blue',
            'proses' => 'amber',
            'selesai' => 'green',
            'dibatalkan' => 'red',
            default => 'gray',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->tanggal_selesai) return false;
        if (in_array($this->status, ['selesai', 'dibatalkan'])) return false;
        return $this->tanggal_selesai->isPast();
    }

    public function getHariTersisaAttribute(): ?int
    {
        if (!$this->tanggal_selesai) return null;
        if (in_array($this->status, ['selesai', 'dibatalkan'])) return null;
        return (int) now()->startOfDay()->diffInDays($this->tanggal_selesai, false);
    }

    public function getBulanNamaAttribute(): string
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ][$this->bulan] ?? '-';
    }

    // ============ HELPER ============

    /**
     * Ambil task per bulan untuk widget kalender.
     * Return: array keyed by day.
     */
    public static function getCalendarData(int $bulan, int $tahun): array
    {
        $tasks = self::byMonth($bulan, $tahun)
            ->with(['assignedUser:id,name'])
            ->orderBy('tanggal_mulai')
            ->get();

        // Group by tanggal_mulai (day only)
        $byDay = [];
        foreach ($tasks as $task) {
            $day = $task->tanggal_mulai->day;
            $byDay[$day][] = [
                'id' => $task->id,
                'judul' => $task->judul,
                'status' => $task->status,
                'status_label' => $task->status_label,
                'status_color' => $task->status_color,
                'assigned_to' => $task->assignedUser?->name,
            ];
        }

        return $byDay;
    }

    /**
     * Statistik per status untuk bulan tertentu.
     */
    public static function getStatsByMonth(int $bulan, int $tahun): array
    {
        $counts = self::byMonth($bulan, $tahun)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'belum' => (int) ($counts['belum'] ?? 0),
            'proses' => (int) ($counts['proses'] ?? 0),
            'selesai' => (int) ($counts['selesai'] ?? 0),
            'dibatalkan' => (int) ($counts['dibatalkan'] ?? 0),
            'total' => array_sum($counts),
        ];
    }
}