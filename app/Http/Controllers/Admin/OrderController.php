<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CollectCashRequest;
use App\Http\Requests\Admin\TransitionOrderRequest;
use App\Models\Order;
use App\Services\Admin\CashCollectionService;
use App\Services\Orders\OrderStatusPresenter;
use App\Services\Orders\OrderStatusTransitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderStatusTransitionService $transitions,
        private readonly OrderStatusPresenter $presenter,
        private readonly CashCollectionService $cash,
    ) {
    }

    public function index(Request $request): View
    {
        $orders = Order::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('fulfillment_type'), fn ($q) => $q->where('fulfillment_type', $request->string('fulfillment_type')))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->string('payment_status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('public_reference', 'like', $term)
                        ->orWhere('customer_name', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->orderByDesc('placed_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'presenter' => $this->presenter,
            'statuses' => config('savoury.order_statuses'),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'payments.collector', 'statusHistories.actor']);

        return view('admin.orders.show', [
            'order' => $order,
            'presenter' => $this->presenter,
            'timeline' => $this->presenter->timeline($order),
            'allowedStatuses' => $this->transitions->allowedTargets($order),
        ]);
    }

    public function transition(TransitionOrderRequest $request, Order $order): RedirectResponse
    {
        $this->transitions->transition(
            $order,
            $request->string('status')->toString(),
            $request->user(),
            $request->input('reason'),
        );

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Order status updated.');
    }

    public function collect(CollectCashRequest $request, Order $order): RedirectResponse
    {
        $this->cash->collect($order, $request->user(), $request->input('notes'));

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Cash marked as collected.');
    }

    public function ticket(Order $order): View
    {
        $order->load('items');

        return view('admin.orders.ticket', [
            'order' => $order,
            'presenter' => $this->presenter,
            'brand' => config('savoury.brand'),
        ]);
    }
}
