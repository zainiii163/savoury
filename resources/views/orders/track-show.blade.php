@extends('layouts.app')

@section('title', 'Order '.$order->public_reference)

@section('content')
    <section class="section">
        <div class="container col-lg-8">
            <div class="info-panel p-4 p-md-5" data-order-tracking data-status-url="{{ route('orders.track.status', $order->public_reference) }}">
                <p class="section-heading__eyebrow mb-2">{{ ucfirst($order->fulfillment_type) }} order</p>
                <h1 class="display-font mb-2">{{ $order->public_reference }}</h1>
                <p class="mb-4">
                    Status:
                    <strong data-order-status-label>{{ $presenter->forOrder($order) }}</strong>
                    · Payment: {{ $order->payment_status }} ({{ str_replace('_', ' ', (string) $order->payment_method) }})
                </p>

                <ol class="order-timeline list-unstyled mb-4" data-order-timeline>
                    @foreach ($timeline as $step)
                        <li class="order-timeline__step order-timeline__step--{{ $step['state'] }}" data-step="{{ $step['key'] }}">
                            <span class="order-timeline__marker" aria-hidden="true"></span>
                            <span class="order-timeline__label">{{ $step['label'] }}</span>
                        </li>
                    @endforeach
                </ol>

                @if (in_array($order->status, ['cancelled', 'rejected'], true))
                    <div class="notice mb-4">This order was {{ $order->status }}. Call {{ $brand['phone'] }} if you need help.</div>
                @endif

                <h2 class="h5">Details</h2>
                <dl class="row mb-4">
                    <dt class="col-sm-4">Customer</dt>
                    <dd class="col-sm-8">{{ $order->customer_name }}</dd>
                    <dt class="col-sm-4">Phone</dt>
                    <dd class="col-sm-8">{{ $order->phone }}</dd>
                    <dt class="col-sm-4">Placed</dt>
                    <dd class="col-sm-8">{{ optional($order->placed_at)?->timezone(config('savoury.display_timezone'))->format('d M Y, h:i A') }}</dd>
                    @if ($order->fulfillment_type === 'delivery')
                        <dt class="col-sm-4">Zone</dt>
                        <dd class="col-sm-8">{{ $order->delivery_zone_name ?: '—' }}</dd>
                        <dt class="col-sm-4">Address</dt>
                        <dd class="col-sm-8">{{ $order->address }}</dd>
                    @endif
                    @if ($order->notes)
                        <dt class="col-sm-4">Notes</dt>
                        <dd class="col-sm-8">{{ $order->notes }}</dd>
                    @endif
                </dl>

                <h2 class="h5">Items</h2>
                <ul class="list-unstyled mb-4">
                    @foreach ($order->items as $item)
                        <li class="d-flex justify-content-between gap-3 mb-2">
                            <span>{{ $item->quantity }} × {{ $item->product_name }}@if($item->variant_name) ({{ $item->variant_name }})@endif</span>
                            <x-price :minor="$item->line_total_minor" />
                        </li>
                    @endforeach
                </ul>

                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <x-price :minor="$order->subtotal_minor" />
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Delivery fee</span>
                    <x-price :minor="$order->delivery_fee_minor" />
                </div>
                <div class="d-flex justify-content-between fw-bold mb-4">
                    <span>Total</span>
                    <x-price :minor="$order->total_minor" />
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-ss-outline" href="{{ route('orders.track') }}">Track another order</a>
                    <a class="btn btn-ss-outline" href="tel:+923166100080">Call {{ $brand['phone'] }}</a>
                </div>
            </div>
        </div>
    </section>
@endsection
