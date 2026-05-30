<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'vehicle_id',
        'user_id',
        'type',
        'title',
        'message',
        'severity',
        'due_date',
        'is_read',
        'is_resolved',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'is_read' => 'boolean',
            'is_resolved' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
