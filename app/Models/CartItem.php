<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CartItem extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_price_minor',
        'addons_total_minor',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_minor' => 'integer',
            'addons_total_minor' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'cart_item_addons')
            ->withPivot('price_minor');
    }

    public function lineTotalMinor(): int
    {
        return ($this->unit_price_minor + $this->addons_total_minor) * $this->quantity;
    }
}
