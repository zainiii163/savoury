<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price_minor',
        'image_path',
        'is_available',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_minor' => 'integer',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'product_addons');
    }

    /**
     * Products that may be exposed to the public catalogue.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereHas('category', fn (Builder $category) => $category->where('is_active', true));
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }
}
