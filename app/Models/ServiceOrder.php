<?php

namespace App\Models;

use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrder extends Model
{
    use Searchable;

    protected $fillable = [
        'order_number',
        'vehicle_id',
        'client_id',
        'mechanic_id',
        'advisor_id',
        'created_by',
        'source',
        'status',
        'progress',
        'priority',
        'description',
        'diagnosis',
        'recommendations',
        'scheduled_at',
        'started_at',
        'completed_at',
        'estimated_cost',
        'total_cost',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'estimated_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    protected function searchableColumns(): array
    {
        return ['order_number', 'description'];
    }

    protected function searchableRelations(): array
    {
        return ['vehicle' => ['plate']];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(OrderComment::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'recibida' => 'Recibida',
            'en_proceso' => 'En proceso',
            'completada' => 'Completado',
            'entregada' => 'Entregado',
            'cancelada' => 'Cancelado',
            default => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'completada', 'entregada' => 'green',
            'en_proceso', 'recibida' => 'yellow',
            'cancelada' => 'red',
            default => 'yellow',
        };
    }

    public static function generateOrderNumber(): string
    {
        $year = now()->year;
        $last = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('OS-%d-%04d', $year, $last);
    }
}
