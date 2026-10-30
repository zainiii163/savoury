@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-0">Orders</h1>
            <p class="small text-secondary mb-0">
                Open inbox:
                <strong data-order-inbox-count>{{ $orders->total() }}</strong>
                <span class="text-secondary" data-order-inbox-stamp></span>
            </p>
        </div>
    </div>

    <div
        hidden
        data-order-inbox
        data-inbox-url="{{ route('admin.api.orders.inbox', request()->only(['status', 'fulfillment_type', 'payment_status'])) }}"
    ></div>

    <form class="row g-2 mb-3" method="get">
        <div class="col-md-3">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Reference, name, phone">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $presenter->label($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="fulfillment_type" class="form-select">
                <option value="">All types</option>
                <option value="pickup" @selected(request('fulfillment_type') === 'pickup')>Pickup</option>
                <option value="delivery" @selected(request('fulfillment_type') === 'delivery')>Delivery</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="payment_status" class="form-select">
                <option value="">All payments</option>
                <option value="unpaid" @selected(request('payment_status') === 'unpaid')>Unpaid</option>
                <option value="paid" @selected(request('payment_status') === 'paid')>Paid</option>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-ss-outline" type="submit">Filter</button>
        </div>
    </form>

    <div class="table-responsive info-panel p-0">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Total</th>
                    <th>Placed</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td><code>{{ $order->public_reference }}</code></td>
                        <td>
                            <strong>{{ $order->customer_name }}</strong>
                            <div class="small text-secondary">{{ $order->phone }}</div>
                        </td>
                        <td>{{ ucfirst($order->fulfillment_type) }}</td>
                        <td>{{ $presenter->label($order->status, $order->fulfillment_type) }}</td>
                        <td>{{ $order->payment_status }}</td>
                        <td><x-price :minor="$order->total_minor" /></td>
                        <td class="small text-secondary">
                            {{ $order->placed_at ? $order->placed_at->timezone(config('savoury.display_timezone'))->format('d M, h:i A') : '—' }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.orders.show', $order) }}">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-secondary p-4">No orders match these filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $orders->links() }}</div>
@endsection
