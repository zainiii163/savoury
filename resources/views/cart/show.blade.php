@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
    <section class="page-hero">
        <div class="container">
            <x-section-heading
                eyebrow="Cart"
                title="Your order"
                text="Quantities and prices are recalculated on the server from the live menu."
            />
            @if (session('status'))
                <div class="notice">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    <section class="section pt-0">
        <div class="container">
            @if ($totals->isEmpty())
                <div class="info-panel text-center p-5">
                    <p class="mb-3">Your cart is empty.</p>
                    <a class="btn btn-ss-primary" href="{{ route('menu.index') }}">Browse menu</a>
                </div>
            @else
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="info-panel p-0 overflow-hidden">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Qty</th>
                                            <th>Line total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($totals->lines as $line)
                                            <tr>
                                                <td>
                                                    <strong>{{ $line->productName }}</strong>
                                                    @if ($line->variantName)
                                                        <div class="small text-secondary">{{ $line->variantName }}</div>
                                                    @endif
                                                    @foreach ($line->addons as $addon)
                                                        <div class="small text-secondary">+ {{ $addon['name'] }}</div>
                                                    @endforeach
                                                    @unless ($line->available)
                                                        <div class="small text-danger">Currently unavailable</div>
                                                    @endunless
                                                    <div class="small"><x-price :minor="$line->unitPriceMinor + $line->addonsTotalMinor" /> each</div>
                                                </td>
                                                <td style="min-width: 8rem;">
                                                    <form method="post" action="{{ route('cart.items.update', $line->key) }}" class="d-flex gap-2">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="number" name="quantity" min="0" max="50" value="{{ $line->quantity }}" class="form-control form-control-sm" aria-label="Quantity for {{ $line->productName }}">
                                                        <button class="btn btn-ss-outline btn-sm" type="submit">Update</button>
                                                    </form>
                                                </td>
                                                <td><x-price :minor="$line->lineTotalMinor()" /></td>
                                                <td class="text-end">
                                                    <form method="post" action="{{ route('cart.items.destroy', $line->key) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-link text-danger p-0" type="submit">Remove</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="info-panel">
                            <h2 class="h5 mb-3">Summary</h2>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <x-price :minor="$totals->subtotalMinor" />
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Delivery fee</span>
                                <x-price :minor="$totals->deliveryFeeMinor" />
                            </div>
                            <div class="d-flex justify-content-between fw-bold mb-3">
                                <span>Total</span>
                                <x-price :minor="$totals->totalMinor" />
                            </div>
                            <p class="small text-secondary">Delivery fees only apply when an owner-configured zone is selected at checkout. No fees have been invented.</p>
                            <a class="btn btn-ss-accent w-100" href="{{ route('checkout.show') }}">Proceed to checkout</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
