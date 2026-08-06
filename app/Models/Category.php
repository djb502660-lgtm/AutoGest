<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function brands(): BelongsToMany
    {
        // There is no direct pivot table between categories and brands.
        // Products act as the linking table (category_id -> products -> brand_id).
        // Define a belongsToMany relation using the products table as the "pivot".
        return $this->belongsToMany(Brand::class, 'products', 'category_id', 'brand_id')->distinct();
    }
}
