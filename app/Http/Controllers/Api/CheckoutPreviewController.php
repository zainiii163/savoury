<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Cart\SessionCartService;
use App\Services\Checkout\OrderingGate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckoutPreviewController extends Controller
{
    public function __construct(
        private readonly SessionCartService $cart,
        private readonly OrderingGate $orderingGate,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fulfillment_type' => ['nullable', 'in:pickup,delivery'],
            'delivery_zone_id' => ['nullable', 'integer', 'exists:delivery_zones,id'],
        ]);

        $fulfillment = $validated['fulfillment_type'] ?? 'pickup';
        $zoneId = $fulfillment === 'delivery'
            ? ($validated['delivery_zone_id'] ?? null)
            : null;

        if ($fulfillment === 'delivery' && empty($zoneId)) {
            throw ValidationException::withMessages([
                'delivery_zone_id' => 'Choose a delivery zone to preview delivery totals.',
            ]);
        }

        $totals = $this->cart->totals($zoneId !== null ? (int) $zoneId : null);

        $orderingOpen = true;
        $orderingMessage = null;

        try {
            $this->orderingGate->assertOpenForOrders();
        } catch (ValidationException $exception) {
            $orderingOpen = false;
            $orderingMessage = collect($exception->errors())->flatten()->first();
        }

        return response()->json([
            'data' => [
                'fulfillment_type' => $fulfillment,
                'ordering_open' => $orderingOpen,
                'ordering_message' => $orderingMessage,
                'cart' => $totals->toArray(),
            ],
        ]);
    }
}
