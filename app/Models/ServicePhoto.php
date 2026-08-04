<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePhoto extends Model
{
    protected $fillable = [
        'service_order_id',
        'user_id',
        'photo_path',
        'description',
        'type',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'reception' => 'Recepción',
            'before' => 'Antes del trabajo',
            'after' => 'Después del trabajo',
            'evidence' => 'Evidencia',
            default => 'General',
        };
    }
}
