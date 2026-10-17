<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username', 'password', 'name', 'email', 'phone', 'avatar', 'role', 'unit', 'active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    // ============================================================
    // ROLE CHECKERS
    // ============================================================

    public function isAdmin(): bool
    {
        return $this->role === 'admin_keuangan';
    }

    public function isKeuangan(): bool
    {
        return $this->role === 'keuangan';
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    // ============================================================
    // ROLE LABEL
    // ============================================================

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin_keuangan' => 'Admin Keuangan',
            'keuangan'       => 'User Keuangan',
            'pimpinan'       => 'Pimpinan',
            'user'           => 'User',
            default          => $this->role,
        };
    }

    // ============================================================
    // ACCESSOR
    // ============================================================

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name, 0, 1));
    }

    // ============================================================
    // RELATIONS
    // ============================================================

    public function kendalas()
    {
        return $this->hasMany(Kendala::class);
    }

    public function realisasis()
    {
        return $this->hasMany(Realisasi::class);
    }

    public function auditTrails()
    {
        return $this->hasMany(AuditTrail::class);
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}