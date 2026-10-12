<?php

namespace App\Traits;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot trait — registrasi event listener untuk model.
     */
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::recordAudit($model, 'CREATE', null, static::formatModel($model));
        });

        static::updated(function ($model) {
            $before = static::formatModel($model->getOriginal());
            $after = static::formatModel($model->getAttributes());

            if ($before === $after) {
                return;
            }

            static::recordAudit($model, 'UPDATE', $before, $after);
        });

        static::deleted(function ($model) {
            static::recordAudit($model, 'DELETE', static::formatModel($model), null);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                static::recordAudit($model, 'RESTORE', null, static::formatModel($model));
            });
        }
    }

    /**
     * Simpan audit trail ke database.
     */
    protected static function recordAudit($model, string $action, ?string $before, ?string $after): void
    {
        $user = Auth::user();

        AuditTrail::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'action' => $action,
            'module' => static::getModuleName($model),
            'record_id' => static::getRecordId($model),
            'before' => $before,
            'after' => $after,
            'ip' => request()->ip() ?? '127.0.0.1',
        ]);
    }

    /**
     * Format model jadi string readable untuk audit.
     */
    protected static function formatModel($data): string
    {
        if (is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }

        if (!is_array($data)) {
            return (string) $data;
        }

        $important = [
            'nama', 'name', 'kode', 'username', 'deskripsi',
            'nominal', 'progress', 'status', 'tahun', 'bulan', 'tanggal',
            'nilai', 'penyedia',
        ];

        $parts = [];
        foreach ($important as $field) {
            if (!isset($data[$field])) continue;

            $value = $data[$field];

            // ⭐ Handle array / object
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }

            // Format nominal
            if ($field === 'nominal' && is_numeric($value)) {
                $value = 'Rp' . number_format((float) $value, 0, ',', '.');
            }

            // Format nilai
            if ($field === 'nilai' && is_numeric($value)) {
                $value = 'Rp' . number_format((float) $value, 0, ',', '.');
            }

            $parts[] = "{$field}: {$value}";
        }

        return implode(' | ', $parts) ?: json_encode($data);
    }

    /**
     * Nama modul untuk audit.
     */
    protected static function getModuleName($model): string
    {
        return class_basename($model);
    }

    /**
     * ID record untuk audit (contoh: TGT-00001).
     */
    protected static function getRecordId($model): string
    {
        $prefix = strtoupper(substr(class_basename($model), 0, 3));
        return $prefix . '-' . str_pad((string) $model->getKey(), 5, '0', STR_PAD_LEFT);
    }
}