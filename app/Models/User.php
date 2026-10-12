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

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKeuangan(): bool
    {
        return $this->role === 'keuangan';
    }

    public function isPimpinan(): bool
    {
        return $this->role === 'pimpinan';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'keuangan' => 'User Keuangan',
            'pimpinan' => 'Pimpinan',
            'viewer' => 'Viewer',
            default => $this->role,
        };
    }

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name, 0, 1));
    }

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

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }
}