<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Concerns\Searchable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, Searchable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    protected function searchableColumns(): array
    {
        return ['name', 'email', 'phone'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'activo');
    }

    public function scopeRole(Builder $query, UserRole|string ...$roles): Builder
    {
        $values = array_map(
            fn (UserRole|string $role) => $role instanceof UserRole ? $role->value : $role,
            $roles,
        );

        return $query->whereIn('role', $values);
    }

    /** Usuarios activos del rol indicado, ordenados por nombre (selects y listados). */
    public static function activeByRole(UserRole|string ...$roles): Builder
    {
        return static::query()->role(...$roles)->active()->orderBy('name');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isMechanic(): bool
    {
        return $this->role === UserRole::Mechanic;
    }

    public function isAdvisor(): bool
    {
        return $this->role === UserRole::Advisor;
    }

    public function isClient(): bool
    {
        return $this->role === UserRole::Client;
    }

    public function isActive(): bool
    {
        return $this->status === 'activo';
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'client_id');
    }

    public function clientOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'client_id');
    }

    public function assignedOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'mechanic_id');
    }

    public function advisorOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'advisor_id');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class, 'mechanic_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function chatbotMessages(): HasMany
    {
        return $this->hasMany(ChatbotMessage::class);
    }

    /** Vehículos accesibles por órdenes o mantenimientos del mecánico. */
    public function accessibleVehicleIds(): \Illuminate\Support\Collection
    {
        return $this->assignedOrders()->pluck('vehicle_id')
            ->merge($this->maintenances()->pluck('vehicle_id'))
            ->unique()
            ->filter();
    }

    public function assignedOrdersQuery()
    {
        return $this->assignedOrders()->with('vehicle', 'client');
    }
}
