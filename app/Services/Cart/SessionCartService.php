<?php

namespace App\Services\Cart;

use App\Models\DeliveryZone;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class SessionCartService
{
    private const SESSION_KEY = 'savoury.cart';

    /**
     * @param  list<int>  $addonIds
     */
    public function add(int $productId, ?int $variantId, array $addonIds, int $quantity = 1): void
    {
        $quantity = max(1, min(50, $quantity));
        $addonIds = $this->normalizeAddonIds($addonIds);
        $this->assertPurchasable($productId, $variantId, $addonIds);

        $key = $this->lineKey($productId, $variantId, $addonIds);
        $items = $this->rawItems();

        if (isset($items[$key])) {
            $items[$key]['quantity'] = min(50, (int) $items[$key]['quantity'] + $quantity);
        } else {
            $items[$key] = [
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'addon_ids' => $addonIds,
                'quantity' => $quantity,
            ];
        }

        $this->store($items);
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        $items = $this->rawItems();

        if (! isset($items[$key])) {
            throw ValidationException::withMessages([
                'cart' => 'That cart item no longer exists.',
            ]);
        }

        if ($quantity <= 0) {
            unset($items[$key]);
        } else {
            $items[$key]['quantity'] = min(50, $quantity);
        }

        $this->store($items);
    }

    public function remove(string $key): void
    {
        $items = $this->rawItems();
        unset($items[$key]);
        $this->store($items);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function totals(?int $deliveryZoneId = null): CartTotals
    {
        $lines = [];
        $raw = $this->rawItems();
        $changed = false;

        foreach ($raw as $key => $item) {
            try {
                $lines[] = $this->hydrateLine($key, $item);
            } catch (ValidationException) {
                unset($raw[$key]);
                $changed = true;
            }
        }

        if ($changed) {
            $this->store($raw);
        }

        $subtotal = array_sum(array_map(fn (CartLine $line) => $line->lineTotalMinor(), $lines));
        $deliveryFee = 0;
        $zoneName = null;

        if ($deliveryZoneId !== null) {
            $zone = DeliveryZone::query()->active()->find($deliveryZoneId);

            if ($zone === null) {
                throw ValidationException::withMessages([
                    'delivery_zone_id' => 'The selected delivery zone is not available.',
                ]);
            }

            if ($zone->min_order_minor > 0 && $subtotal < $zone->min_order_minor) {
                throw ValidationException::withMessages([
                    'delivery_zone_id' => 'Your cart is below the minimum order for this delivery zone.',
                ]);
            }

            $deliveryFee = (int) $zone->delivery_fee_minor;
            $zoneName = $zone->name;
        }

        return new CartTotals(
            lines: array_values($lines),
            subtotalMinor: $subtotal,
            deliveryFeeMinor: $deliveryFee,
            totalMinor: $subtotal + $deliveryFee,
            deliveryZoneId: $deliveryZoneId,
            deliveryZoneName: $zoneName,
        );
    }

    public function count(): int
    {
        return array_sum(array_map(fn (array $item) => (int) $item['quantity'], $this->rawItems()));
    }

    /**
     * @param  list<int>  $addonIds
     */
    public function lineKey(int $productId, ?int $variantId, array $addonIds): string
    {
        $addonIds = $this->normalizeAddonIds($addonIds);

        return hash('sha256', json_encode([
            $productId,
            $variantId,
            $addonIds,
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * @param  array{product_id: int, product_variant_id: ?int, addon_ids: list<int>, quantity: int}  $item
     */
    private function hydrateLine(string $key, array $item): CartLine
    {
        $productId = (int) $item['product_id'];
        $variantId = isset($item['product_variant_id']) ? (int) $item['product_variant_id'] : null;
        $addonIds = $this->normalizeAddonIds($item['addon_ids'] ?? []);
        $quantity = max(1, min(50, (int) $item['quantity']));

        $product = Product::query()
            ->with(['category', 'variants', 'addons'])
            ->find($productId);

        if ($product === null || ! $product->is_active || $product->category?->is_active === false) {
            throw ValidationException::withMessages([
                'cart' => 'A cart item is no longer available.',
            ]);
        }

        $variant = null;
        $unitPrice = (int) $product->price_minor;

        if ($variantId !== null) {
            $variant = $product->variants->firstWhere('id', $variantId);

            if ($variant === null || ! $variant->is_available) {
                throw ValidationException::withMessages([
                    'cart' => 'A selected option is no longer available.',
                ]);
            }

            $unitPrice = (int) $variant->price_minor;
        }

        $addons = [];
        $addonsTotal = 0;

        if ($addonIds !== []) {
            $allowed = $product->addons->where('is_active', true)->keyBy('id');

            foreach ($addonIds as $addonId) {
                $addon = $allowed->get($addonId);

                if ($addon === null) {
                    throw ValidationException::withMessages([
                        'cart' => 'A selected add-on is no longer available.',
                    ]);
                }

                $addons[] = [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'price_minor' => (int) $addon->price_minor,
                ];
                $addonsTotal += (int) $addon->price_minor;
            }
        }

        return new CartLine(
            key: $key,
            productId: $product->id,
            productName: $product->name,
            productSlug: $product->slug,
            variantId: $variant?->id,
            variantName: $variant?->name,
            addonIds: $addonIds,
            addons: $addons,
            quantity: $quantity,
            unitPriceMinor: $unitPrice,
            addonsTotalMinor: $addonsTotal,
            available: (bool) $product->is_available,
        );
    }

    /**
     * @param  list<int>  $addonIds
     */
    private function assertPurchasable(int $productId, ?int $variantId, array $addonIds): void
    {
        $product = Product::query()
            ->visible()
            ->available()
            ->with(['variants', 'addons'])
            ->find($productId);

        if ($product === null) {
            throw ValidationException::withMessages([
                'product_id' => 'That product is not available.',
            ]);
        }

        if ($variantId !== null) {
            $variant = $product->variants->first(
                fn (ProductVariant $candidate) => $candidate->id === $variantId && $candidate->is_available
            );

            if ($variant === null) {
                throw ValidationException::withMessages([
                    'product_variant_id' => 'That product option is not available.',
                ]);
            }
        }

        if ($addonIds !== []) {
            $allowedIds = $product->addons->where('is_active', true)->pluck('id')->all();

            foreach ($addonIds as $addonId) {
                if (! in_array($addonId, $allowedIds, true)) {
                    throw ValidationException::withMessages([
                        'addon_ids' => 'One or more add-ons are not available for this product.',
                    ]);
                }
            }
        }
    }

    /**
     * @param  list<int|string>  $addonIds
     * @return list<int>
     */
    private function normalizeAddonIds(array $addonIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $addonIds)));
        sort($ids);

        return $ids;
    }

    /**
     * @return array<string, array{product_id: int, product_variant_id: ?int, addon_ids: list<int>, quantity: int}>
     */
    private function rawItems(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    /**
     * @param  array<string, array{product_id: int, product_variant_id: ?int, addon_ids: list<int>, quantity: int}>  $items
     */
    private function store(array $items): void
    {
        Session::put(self::SESSION_KEY, $items);
    }
}
