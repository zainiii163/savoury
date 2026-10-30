@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="section-heading__eyebrow mb-1">Operations</p>
            <h1 class="h2 mb-1">Dashboard</h1>
            <p class="text-secondary mb-0">
                Live counts from MySQL · {{ $metrics['as_of']->format('d M Y, h:i A') }} {{ $metrics['timezone'] }}
            </p>
        </div>
        @if ($metrics['ordering_paused'])
            <div class="notice mb-0">Ordering is paused. Resume it in settings (Phase 8).</div>
        @endif
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="info-panel">
                <div class="text-secondary small">Orders today</div>
                <div class="display-font fs-2">{{ $metrics['today']['order_count'] }}</div>
                <div class="small text-secondary">
                    {{ $metrics['today']['pickup_count'] }} pickup · {{ $metrics['today']['delivery_count'] }} delivery
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="info-panel">
                <div class="text-secondary small">Sales today</div>
                <div class="display-font fs-2"><x-price :minor="$metrics['today']['sales_minor']" /></div>
                <div class="small text-secondary">Excludes cancelled / rejected</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="info-panel">
                <div class="text-secondary small">Open orders</div>
                <div class="display-font fs-2">{{ $metrics['open_orders']['total'] }}</div>
                <div class="small text-secondary">Not yet completed or closed</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="info-panel">
                <div class="text-secondary small">Unpaid cash</div>
                <div class="display-font fs-2"><x-price :minor="$metrics['cash']['unpaid_minor']" /></div>
                <div class="small text-secondary">Outstanding COD / pickup cash</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="info-panel h-100">
                <h2 class="h5 mb-3">Open by status</h2>
                <ul class="list-unstyled mb-0 d-grid gap-2">
                    @foreach ($metrics['open_orders']['by_status'] as $status => $count)
                        <li class="d-flex justify-content-between gap-3">
                            <span>{{ $presenter->label($status) }}</span>
                            <strong>{{ $count }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="info-panel h-100">
                <h2 class="h5 mb-3">Catalogue snapshot</h2>
                <div class="d-flex flex-wrap gap-4">
                    <div>
                        <div class="text-secondary small">Active products</div>
                        <div class="fs-4 fw-bold">{{ $metrics['catalogue']['active_products'] }}</div>
                    </div>
                    <div>
                        <div class="text-secondary small">Marked unavailable</div>
                        <div class="fs-4 fw-bold">{{ $metrics['catalogue']['unavailable_products'] }}</div>
                    </div>
                </div>
                <p class="small text-secondary mt-3 mb-0">
                    Use Orders for status changes and cash collection. Settings covers hours, zones, and pause.
                </p>
                @if (auth()->user()?->canManageOrders())
                    <a class="btn btn-ss-outline btn-sm mt-3" href="{{ route('admin.orders.index') }}">Open order inbox</a>
                @endif
            </div>
        </div>
    </div>

    <div class="info-panel p-0 overflow-hidden">
        <div class="p-3 border-bottom">
            <h2 class="h5 mb-0">Recent orders</h2>
        </div>
        @if ($metrics['recent_orders']->isEmpty())
            <div class="p-4 text-secondary">No orders yet.</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Total</th>
                            <th>Placed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($metrics['recent_orders'] as $order)
                            <tr>
                                <td><code>{{ $order->public_reference }}</code></td>
                                <td>{{ $order->customer_name }}</td>
                                <td>{{ ucfirst($order->fulfillment_type) }}</td>
                                <td>{{ $presenter->label($order->status, $order->fulfillment_type) }}</td>
                                <td>{{ $order->payment_status }}</td>
                                <td><x-price :minor="$order->total_minor" /></td>
                                <td class="small text-secondary">
                                    {{ $order->placed_at ? $order->placed_at->timezone($metrics['timezone'])->format('d M, h:i A') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
