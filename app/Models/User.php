<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles()->where('slug', $slug)->exists();
    }

    public function hasPermission(string $slug): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->hasRole('admin')) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('slug', $slug))
            ->exists();
    }

    public function canManageMenu(): bool
    {
        return $this->hasPermission('manage-menu');
    }

    public function canManageOrders(): bool
    {
        return $this->hasPermission('manage-orders');
    }

    public function canCollectCash(): bool
    {
        return $this->hasPermission('collect-cash');
    }

    public function canManageSettings(): bool
    {
        return $this->hasPermission('manage-settings');
    }

    public function canViewReports(): bool
    {
        return $this->hasPermission('view-reports');
    }

    public function canAccessAdmin(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return $this->canManageMenu()
            || $this->canManageOrders()
            || $this->canCollectCash()
            || $this->canManageSettings()
            || $this->canViewReports()
            || $this->hasPermission('view-audit-logs');
    }
}
