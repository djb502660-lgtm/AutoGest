<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isMechanic(): bool
    {
        return $this->role === UserRole::Mechanic;
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
}
