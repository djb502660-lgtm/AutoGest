<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicePhoto extends Model
{
    use SoftDeletes;

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

    public function getUrlAttribute(): string
    {
        $path = ltrim(str_replace('\\', '/', (string) $this->photo_path), '/');

        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return '/'.$path;
        }

        return '/storage/'.$path;
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

    public function lightboxCaption(): string
    {
        return $this->type_label;
    }

    public function lightboxMeta(): string
    {
        return collect([
            $this->user?->name,
            $this->created_at?->format('d/m/Y H:i'),
        ])->filter()->implode(' · ');
    }
}
