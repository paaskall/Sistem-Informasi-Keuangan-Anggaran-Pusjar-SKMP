<?php

namespace App\Observers;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

class AuditableObserver
{
    public function created($model): void
    {
        $this->record($model, 'CREATE', null, $this->format($model));
    }

    public function updated($model): void
    {
        $before = $this->format($model->getOriginal());
        $after = $this->format($model->getAttributes());

        if ($before === $after) {
            return;
        }

        $this->record($model, 'UPDATE', $before, $after);
    }

    public function deleted($model): void
    {
        $this->record($model, 'DELETE', $this->format($model), null);
    }

    public function restored($model): void
    {
        $this->record($model, 'RESTORE', null, $this->format($model));
    }

    public function forceDeleted($model): void
    {
        $this->record($model, 'DELETE', $this->format($model), null);
    }

    protected function record($model, string $action, ?string $before, ?string $after): void
    {
        $user = Auth::user();

        AuditTrail::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'action' => $action,
            'module' => class_basename($model),
            'record_id' => $this->recordId($model),
            'before' => $before,
            'after' => $after,
            'ip' => request()->ip() ?? '127.0.0.1',
        ]);
    }

    protected function format($data): string
    {
        if (is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }

        if (!is_array($data)) {
            return (string) $data;
        }

        // Kecualikan field sensitif (password, token)
        $hidden = ['password', 'remember_token', 'two_factor_secret'];
        foreach ($hidden as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[HIDDEN]';
            }
        }

        $important = [
            'nama', 'name', 'kode', 'username', 'email', 'unit', 'role', 'active',
            'deskripsi', 'nominal', 'progress', 'status', 'tahun', 'bulan',
            'tanggal', 'nilai', 'penyedia', 'target', 'realisasi',
            'peserta_target', 'peserta_realisasi',
        ];

        $parts = [];
        foreach ($important as $field) {
            if (!isset($data[$field])) continue;

            $value = $data[$field];

            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }

            if (in_array($field, ['nominal', 'nilai', 'target', 'realisasi']) && is_numeric($value)) {
                $value = 'Rp' . number_format((float) $value, 0, ',', '.');
            }

            $parts[] = "{$field}: {$value}";
        }

        return implode(' | ', $parts) ?: json_encode($data);
    }

    protected function recordId($model): string
    {
        $prefix = strtoupper(substr(class_basename($model), 0, 3));
        return $prefix . '-' . str_pad((string) $model->getKey(), 5, '0', STR_PAD_LEFT);
    }
}