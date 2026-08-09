<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Stringable;

trait Searchable
{
    /**
     * Columnas propias del modelo incluidas en la búsqueda libre.
     *
     * @return array<int, string>
     */
    protected function searchableColumns(): array
    {
        return [];
    }

    /**
     * Columnas de relaciones incluidas en la búsqueda libre.
     *
     * @return array<string, array<int, string>>
     */
    protected function searchableRelations(): array
    {
        return [];
    }

    public function scopeSearch(Builder $query, string|Stringable|null $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $columns = $this->searchableColumns();
        $relations = $this->searchableRelations();

        return $query->where(function (Builder $query) use ($columns, $relations, $term) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', "%{$term}%");
            }

            foreach ($relations as $relation => $relationColumns) {
                $query->orWhereHas($relation, function (Builder $related) use ($relationColumns, $term) {
                    $related->where(function (Builder $related) use ($relationColumns, $term) {
                        foreach ($relationColumns as $column) {
                            $related->orWhere($column, 'like', "%{$term}%");
                        }
                    });
                });
            }
        });
    }
}
