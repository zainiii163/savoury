<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_reference',
        'idempotency_key',
        'tracking_token_hash',
        'user_id',
        'customer_name',
        'phone',
        'fulfillment_type',
        'payment_method',
        'delivery_zone_id',
        'delivery_zone_name',
        'address',
        'notes',
        'subtotal_minor',
        'delivery_fee_minor',
        'total_minor',
        'status',
        'payment_status',
        'placed_at',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal_minor' => 'integer',
            'delivery_fee_minor' => 'integer',
            'total_minor' => 'integer',
            'placed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
