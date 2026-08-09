<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasActiveFlag
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Catálogo activo ordenado por nombre, listo para selects y listados. */
    public function scopeCatalog(Builder $query): Builder
    {
        return $query->active()->orderBy('name');
    }
}
