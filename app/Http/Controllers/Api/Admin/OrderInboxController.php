<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Orders\OrderStatusPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderInboxController extends Controller
{
    public function __invoke(Request $request, OrderStatusPresenter $presenter): JsonResponse
    {
        $orders = Order::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('fulfillment_type'), fn ($q) => $q->where('fulfillment_type', $request->string('fulfillment_type')))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->string('payment_status')))
            ->when(
                ! $request->boolean('include_closed') && ! $request->filled('status'),
                fn ($q) => $q->whereNotIn('status', ['delivered', 'collected', 'cancelled', 'rejected'])
            )
            ->orderByDesc('placed_at')
            ->limit(50)
            ->get([
                'id',
                'public_reference',
                'customer_name',
                'phone',
                'fulfillment_type',
                'status',
                'payment_status',
                'total_minor',
                'placed_at',
            ]);

        return response()->json([
            'data' => $orders->map(fn (Order $order) => [
                'id' => $order->id,
                'public_reference' => $order->public_reference,
                'customer_name' => $order->customer_name,
                'phone' => $order->phone,
                'fulfillment_type' => $order->fulfillment_type,
                'status' => $order->status,
                'status_label' => $presenter->forOrder($order),
                'payment_status' => $order->payment_status,
                'total_minor' => $order->total_minor,
                'total_pkr' => \App\Support\Money::toMajor((int) $order->total_minor),
                'placed_at' => $order->placed_at
                    ? $order->placed_at->timezone(config('savoury.display_timezone'))->toIso8601String()
                    : null,
                'show_url' => route('admin.orders.show', $order),
            ])->values(),
            'meta' => [
                'count' => $orders->count(),
                'generated_at' => now(config('savoury.display_timezone'))->toIso8601String(),
            ],
        ]);
    }
}
