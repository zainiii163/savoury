<?php

namespace App\Http\Controllers;

use App\Http\Requests\Orders\TrackOrderRequest;
use App\Services\Orders\OrderStatusPresenter;
use App\Services\Orders\OrderTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function __construct(
        private readonly OrderTrackingService $tracking,
        private readonly OrderStatusPresenter $presenter,
    ) {
    }

    public function create(Request $request): View
    {
        $reference = strtoupper((string) $request->query('reference', ''));

        return view('orders.track', [
            'brand' => config('savoury.brand'),
            'reference' => $reference,
            'order' => null,
        ]);
    }

    public function store(TrackOrderRequest $request): RedirectResponse
    {
        $order = $this->tracking->findVerified(
            $request->string('public_reference')->toString(),
            $request->string('tracking_token')->toString(),
        );

        $this->tracking->grant($request, $order->public_reference);

        return redirect()->route('orders.track.show', $order->public_reference);
    }

    public function show(Request $request, string $reference): View|RedirectResponse
    {
        if (! $this->tracking->hasAccess($request, $reference)) {
            return redirect()
                ->route('orders.track', ['reference' => $reference])
                ->withErrors(['tracking_token' => 'Enter your order reference and tracking token to view this order.']);
        }

        $order = $this->tracking->requireAccess($request, $reference);

        return view('orders.track-show', [
            'order' => $order,
            'presenter' => $this->presenter,
            'timeline' => $this->presenter->timeline($order),
            'statusPayload' => $this->tracking->statusPayload($order),
            'brand' => config('savoury.brand'),
        ]);
    }

    public function status(Request $request, string $reference): JsonResponse
    {
        if (! $this->tracking->hasAccess($request, $reference)) {
            return response()->json([
                'message' => 'Tracking session required.',
            ], 403);
        }

        $order = $this->tracking->requireAccess($request, $reference);

        return response()->json($this->tracking->statusPayload($order));
    }
}
