<?php

namespace App\Services\Checkout;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Cart\CartLine;
use App\Services\Cart\SessionCartService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly SessionCartService $cart,
        private readonly OrderingGate $orderingGate,
    ) {
    }

    /**
     * @param  array{
     *     customer_name: string,
     *     phone: string,
     *     fulfillment_type: string,
     *     payment_method: string,
     *     delivery_zone_id?: int|null,
     *     address?: string|null,
     *     notes?: string|null,
     *     idempotency_key: string
     * }  $input
     * @return array{order: Order, tracking_token: string}
     */
    public function place(array $input): array
    {
        $this->orderingGate->assertOpenForOrders();

        $existing = Order::query()
            ->where('idempotency_key', $input['idempotency_key'])
            ->first();

        if ($existing !== null) {
            return [
                'order' => $existing->load(['items', 'payments']),
                'tracking_token' => '',
            ];
        }

        $fulfillment = $input['fulfillment_type'];
        $paymentMethod = $input['payment_method'];

        if ($fulfillment === 'delivery' && $paymentMethod !== 'cash_on_delivery') {
            throw ValidationException::withMessages([
                'payment_method' => 'Delivery orders must use cash on delivery.',
            ]);
        }

        if ($fulfillment === 'pickup' && $paymentMethod !== 'cash_on_pickup') {
            throw ValidationException::withMessages([
                'payment_method' => 'Pickup orders must use cash on pickup.',
            ]);
        }

        $zoneId = $fulfillment === 'delivery' ? ($input['delivery_zone_id'] ?? null) : null;

        if ($fulfillment === 'delivery' && empty($zoneId)) {
            throw ValidationException::withMessages([
                'delivery_zone_id' => 'Please choose a delivery zone. Delivery is unavailable until the owner configures zones.',
            ]);
        }

        if ($fulfillment === 'delivery' && blank($input['address'] ?? null)) {
            throw ValidationException::withMessages([
                'address' => 'A delivery address is required.',
            ]);
        }

        $totals = $this->cart->totals($zoneId !== null ? (int) $zoneId : null);

        if ($totals->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        foreach ($totals->lines as $line) {
            if (! $line->available) {
                throw ValidationException::withMessages([
                    'cart' => "{$line->productName} is currently unavailable.",
                ]);
            }
        }

        $trackingToken = Str::random(48);
        $trackingHash = hash('sha256', $trackingToken);

        $order = DB::transaction(function () use ($input, $totals, $fulfillment, $paymentMethod, $trackingHash) {
            $order = Order::query()->create([
                'public_reference' => $this->uniqueReference(),
                'idempotency_key' => $input['idempotency_key'],
                'tracking_token_hash' => $trackingHash,
                'customer_name' => $input['customer_name'],
                'phone' => $input['phone'],
                'fulfillment_type' => $fulfillment,
                'payment_method' => $paymentMethod,
                'delivery_zone_id' => $totals->deliveryZoneId,
                'delivery_zone_name' => $totals->deliveryZoneName,
                'address' => $fulfillment === 'delivery' ? $input['address'] : null,
                'notes' => $input['notes'] ?? null,
                'subtotal_minor' => $totals->subtotalMinor,
                'delivery_fee_minor' => $totals->deliveryFeeMinor,
                'total_minor' => $totals->totalMinor,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'placed_at' => now('UTC'),
            ]);

            foreach ($totals->lines as $line) {
                /** @var CartLine $line */
                $order->items()->create([
                    'product_id' => $line->productId,
                    'product_variant_id' => $line->variantId,
                    'product_name' => $line->productName,
                    'variant_name' => $line->variantName,
                    'unit_price_minor' => $line->unitPriceMinor,
                    'addons_total_minor' => $line->addonsTotalMinor,
                    'quantity' => $line->quantity,
                    'line_total_minor' => $line->lineTotalMinor(),
                    'addons_snapshot' => $line->addons,
                ]);
            }

            $order->statusHistories()->create([
                'from_status' => null,
                'to_status' => 'pending',
                'reason' => 'Order placed by customer',
                'created_at' => now('UTC'),
            ]);

            Payment::query()->create([
                'order_id' => $order->id,
                'method' => $paymentMethod,
                'amount_minor' => $totals->totalMinor,
                'status' => 'unpaid',
            ]);

            return $order->load(['items', 'payments']);
        });

        $this->cart->clear();

        return [
            'order' => $order,
            'tracking_token' => $trackingToken,
        ];
    }

    private function uniqueReference(): string
    {
        do {
            $reference = 'SS-'.now(config('savoury.display_timezone'))->format('ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('public_reference', $reference)->exists());

        return $reference;
    }
}
