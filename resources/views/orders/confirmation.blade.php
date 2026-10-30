@extends('layouts.app')

@section('title', 'Order confirmed')

@section('content')
    <section class="section">
        <div class="container col-lg-8">
            <div class="info-panel p-4 p-md-5">
                <p class="section-heading__eyebrow mb-2">Confirmed</p>
                <h1 class="display-font mb-3">Thank you, {{ $order->customer_name }}</h1>
                <p class="mb-4">Your order <strong>{{ $order->public_reference }}</strong> has been placed and saved. Payment status: unpaid ({{ str_replace('_', ' ', (string) $order->payment_method) }}).</p>

                <div class="notice mb-4">
                    <strong>Save your tracking token now.</strong> It is shown once and is not stored in plaintext on the server.
                    <div class="mt-2"><code class="user-select-all">{{ $trackingToken !== '' ? $trackingToken : 'Reused duplicate submission — use the token from your first confirmation.' }}</code></div>
                </div>

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
                    <a class="btn btn-ss-primary" href="{{ route('orders.track.show', $order->public_reference) }}">Track this order</a>
                    <a class="btn btn-ss-outline" href="{{ route('menu.index') }}">Order more</a>
                    <a class="btn btn-ss-outline" href="tel:+923166100080">Call {{ $brand['phone'] }}</a>
                </div>
            </div>
        </div>
    </section>
@endsection
