<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Models\DeliveryZone;
use App\Services\Cart\SessionCartService;
use App\Services\Checkout\CheckoutService;
use App\Services\Orders\OrderTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly SessionCartService $cart,
        private readonly CheckoutService $checkout,
        private readonly OrderTrackingService $tracking,
    ) {
    }

    public function show(Request $request): View|RedirectResponse
    {
        $totals = $this->cart->totals(
            $request->filled('delivery_zone_id') ? $request->integer('delivery_zone_id') : null
        );

        if ($totals->isEmpty()) {
            return redirect()
                ->route('cart.show')
                ->withErrors(['cart' => 'Your cart is empty.']);
        }

        return view('checkout.show', [
            'totals' => $totals,
            'zones' => DeliveryZone::query()->active()->orderBy('name')->get(),
            'idempotencyKey' => (string) Str::uuid(),
            'brand' => config('savoury.brand'),
        ]);
    }

    public function store(PlaceOrderRequest $request): RedirectResponse
    {
        $result = $this->checkout->place($request->validated());

        $request->session()->put('savoury.latest_order', [
            'reference' => $result['order']->public_reference,
            'tracking_token' => $result['tracking_token'],
        ]);

        $this->tracking->grant($request, $result['order']->public_reference);

        return redirect()->route('orders.confirmation', $result['order']->public_reference);
    }

    public function confirmation(Request $request, string $reference): View|RedirectResponse
    {
        $latest = $request->session()->get('savoury.latest_order');

        if (! is_array($latest) || ($latest['reference'] ?? null) !== $reference) {
            return redirect()
                ->route('orders.track')
                ->withErrors(['order' => 'Order confirmation is only available right after checkout. Use tracking with your secure token.']);
        }

        $order = \App\Models\Order::query()
            ->with('items')
            ->where('public_reference', $reference)
            ->firstOrFail();

        return view('orders.confirmation', [
            'order' => $order,
            'trackingToken' => $latest['tracking_token'] ?? '',
            'brand' => config('savoury.brand'),
        ]);
    }
}
