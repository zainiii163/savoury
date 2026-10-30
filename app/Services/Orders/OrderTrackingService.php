<?php

namespace App\Services\Orders;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderTrackingService
{
    private const SESSION_PREFIX = 'savoury.tracking.';

    public function __construct(
        private readonly OrderStatusPresenter $presenter,
    ) {
    }

    public function findVerified(string $reference, string $token): Order
    {
        $order = Order::query()
            ->with(['items', 'statusHistories' => fn ($q) => $q->orderBy('created_at')])
            ->where('public_reference', $reference)
            ->first();

        if ($order === null || ! hash_equals((string) $order->tracking_token_hash, hash('sha256', $token))) {
            throw ValidationException::withMessages([
                'tracking_token' => 'We could not find an order with that reference and tracking token.',
            ]);
        }

        return $order;
    }

    public function grant(Request $request, string $reference): void
    {
        $request->session()->put(self::SESSION_PREFIX.$reference, true);
    }

    public function revoke(Request $request, string $reference): void
    {
        $request->session()->forget(self::SESSION_PREFIX.$reference);
    }

    public function hasAccess(Request $request, string $reference): bool
    {
        return (bool) $request->session()->get(self::SESSION_PREFIX.$reference, false);
    }

    public function requireAccess(Request $request, string $reference): Order
    {
        if (! $this->hasAccess($request, $reference)) {
            throw ValidationException::withMessages([
                'tracking_token' => 'Enter your order reference and tracking token to view this order.',
            ]);
        }

        return Order::query()
            ->with(['items', 'statusHistories' => fn ($q) => $q->orderBy('created_at')])
            ->where('public_reference', $reference)
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    public function statusPayload(Order $order): array
    {
        return [
            'public_reference' => $order->public_reference,
            'fulfillment_type' => $order->fulfillment_type,
            'status' => $order->status,
            'status_label' => $this->presenter->forOrder($order),
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'placed_at' => $order->placed_at
                ? $order->placed_at->timezone(config('savoury.display_timezone'))->toIso8601String()
                : null,
            'completed_at' => $order->completed_at
                ? $order->completed_at->timezone(config('savoury.display_timezone'))->toIso8601String()
                : null,
            'timeline' => $this->presenter->timeline($order),
            'is_terminal' => in_array($order->status, ['delivered', 'collected', 'cancelled', 'rejected'], true),
        ];
    }
}
