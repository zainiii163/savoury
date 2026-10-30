<?php

namespace App\Services\Cart;

use App\Support\Money;

final class CartTotals
{
    /**
     * @param  list<CartLine>  $lines
     */
    public function __construct(
        public readonly array $lines,
        public readonly int $subtotalMinor,
        public readonly int $deliveryFeeMinor,
        public readonly int $totalMinor,
        public readonly ?int $deliveryZoneId = null,
        public readonly ?string $deliveryZoneName = null,
    ) {
    }

    public function itemCount(): int
    {
        return array_sum(array_map(fn (CartLine $line) => $line->quantity, $this->lines));
    }

    public function isEmpty(): bool
    {
        return $this->lines === [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'items' => array_map(fn (CartLine $line) => $line->toArray(), $this->lines),
            'item_count' => $this->itemCount(),
            'subtotal_minor' => $this->subtotalMinor,
            'subtotal_pkr' => Money::toMajor($this->subtotalMinor),
            'delivery_fee_minor' => $this->deliveryFeeMinor,
            'delivery_fee_pkr' => Money::toMajor($this->deliveryFeeMinor),
            'total_minor' => $this->totalMinor,
            'total_pkr' => Money::toMajor($this->totalMinor),
            'delivery_zone_id' => $this->deliveryZoneId,
            'delivery_zone_name' => $this->deliveryZoneName,
            'currency' => config('savoury.currency'),
        ];
    }
}
