<?php

namespace App\Services\Cart;

use App\Support\Money;

final class CartLine
{
    /**
     * @param  list<int>  $addonIds
     * @param  list<array{id: int, name: string, price_minor: int}>  $addons
     */
    public function __construct(
        public readonly string $key,
        public readonly int $productId,
        public readonly string $productName,
        public readonly string $productSlug,
        public readonly ?int $variantId,
        public readonly ?string $variantName,
        public readonly array $addonIds,
        public readonly array $addons,
        public readonly int $quantity,
        public readonly int $unitPriceMinor,
        public readonly int $addonsTotalMinor,
        public readonly bool $available,
    ) {
    }

    public function lineTotalMinor(): int
    {
        return ($this->unitPriceMinor + $this->addonsTotalMinor) * $this->quantity;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'product_slug' => $this->productSlug,
            'variant_id' => $this->variantId,
            'variant_name' => $this->variantName,
            'addon_ids' => $this->addonIds,
            'addons' => array_map(
                fn (array $addon) => [
                    'id' => $addon['id'],
                    'name' => $addon['name'],
                    'price_minor' => $addon['price_minor'],
                    'price_pkr' => Money::toMajor($addon['price_minor']),
                ],
                $this->addons
            ),
            'quantity' => $this->quantity,
            'unit_price_minor' => $this->unitPriceMinor,
            'unit_price_pkr' => Money::toMajor($this->unitPriceMinor),
            'addons_total_minor' => $this->addonsTotalMinor,
            'addons_total_pkr' => Money::toMajor($this->addonsTotalMinor),
            'line_total_minor' => $this->lineTotalMinor(),
            'line_total_pkr' => Money::toMajor($this->lineTotalMinor()),
            'available' => $this->available,
        ];
    }
}
