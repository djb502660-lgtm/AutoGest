<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'vehicle_id',
        'user_id',
        'appointment_request_id',
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

    public function appointmentRequest(): BelongsTo
    {
        return $this->belongsTo(AppointmentRequest::class);
    }

    public static function markChatbotAppointmentHandled(AppointmentRequest $appointment): void
    {
        static::query()
            ->where(function ($query) use ($appointment) {
                $query->where('appointment_request_id', $appointment->id);

                if ($appointment->vehicle_id) {
                    $query->orWhere(function ($fallback) use ($appointment) {
                        $fallback->whereNull('appointment_request_id')
                            ->where('vehicle_id', $appointment->vehicle_id)
                            ->where('title', 'like', '%chatbot%');
                    });
                }
            })
            ->where('is_resolved', false)
            ->update([
                'is_read' => true,
                'is_resolved' => true,
                'resolved_at' => now(),
            ]);
    }
}
