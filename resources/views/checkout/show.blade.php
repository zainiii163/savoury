@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <section class="page-hero">
        <div class="container">
            <x-section-heading
                eyebrow="Checkout"
                title="Place your order"
                text="Cash on delivery or cash on pickup only. Totals are calculated on the server."
            />
            @if ($errors->any())
                <div class="alert alert-danger">
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
            <form
                method="post"
                action="{{ route('checkout.store') }}"
                class="row g-4"
                id="checkout-form"
                data-checkout-preview-url="{{ route('api.checkout.preview') }}"
            >
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', $idempotencyKey) }}">

                <div class="col-lg-7">
                    <div class="info-panel">
                        <h2 class="h5 mb-3">Customer details</h2>
                        <div class="mb-3">
                            <label class="form-label" for="customer_name">Full name</label>
                            <input id="customer_name" name="customer_name" type="text" class="form-control" value="{{ old('customer_name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="phone">Phone</label>
                            <input id="phone" name="phone" type="tel" class="form-control" value="{{ old('phone') }}" placeholder="03166100080" required>
                        </div>

                        <fieldset class="mb-3">
                            <legend class="form-label">Fulfilment</legend>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="fulfillment_type" id="fulfillment_pickup" value="pickup" @checked(old('fulfillment_type', 'pickup') === 'pickup')>
                                <label class="form-check-label" for="fulfillment_pickup">Pickup</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="fulfillment_type" id="fulfillment_delivery" value="delivery" @checked(old('fulfillment_type') === 'delivery') @disabled($zones->isEmpty())>
                                <label class="form-check-label" for="fulfillment_delivery">
                                    Delivery
                                    @if ($zones->isEmpty())
                                        <span class="text-secondary">(unavailable until owner configures zones)</span>
                                    @endif
                                </label>
                            </div>
                        </fieldset>

                        <div class="mb-3" data-delivery-fields>
                            <label class="form-label" for="delivery_zone_id">Delivery zone</label>
                            <select id="delivery_zone_id" name="delivery_zone_id" class="form-select" @disabled($zones->isEmpty())>
                                <option value="">Select zone</option>
                                @foreach ($zones as $zone)
                                    <option value="{{ $zone->id }}" @selected((string) old('delivery_zone_id') === (string) $zone->id)>
                                        {{ $zone->name }} — fee <x-price :minor="$zone->delivery_fee_minor" />
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3" data-delivery-fields>
                            <label class="form-label" for="address">Delivery address</label>
                            <textarea id="address" name="address" rows="3" class="form-control">{{ old('address') }}</textarea>
                        </div>

                        <fieldset class="mb-3">
                            <legend class="form-label">Payment</legend>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="pay_pickup" value="cash_on_pickup" @checked(old('payment_method', 'cash_on_pickup') === 'cash_on_pickup')>
                                <label class="form-check-label" for="pay_pickup">Cash on pickup</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="pay_delivery" value="cash_on_delivery" @checked(old('payment_method') === 'cash_on_delivery')>
                                <label class="form-check-label" for="pay_delivery">Cash on delivery</label>
                            </div>
                        </fieldset>

                        <div class="mb-0">
                            <label class="form-label" for="notes">Order notes (optional)</label>
                            <textarea id="notes" name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="info-panel">
                        <h2 class="h5 mb-3">Order summary</h2>
                        <ul class="list-unstyled mb-3">
                            @foreach ($totals->lines as $line)
                                <li class="d-flex justify-content-between gap-3 mb-2">
                                    <span>{{ $line->quantity }} × {{ $line->productName }}</span>
                                    <x-price :minor="$line->lineTotalMinor()" />
                                </li>
                            @endforeach
                        </ul>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <x-price :minor="$totals->subtotalMinor" />
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery fee</span>
                            <span data-checkout-delivery-fee class="text-secondary">Calculated for selected zone</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold mb-3">
                            <span>Estimated total</span>
                            <span data-checkout-total><x-price :minor="$totals->totalMinor" /></span>
                        </div>
                        <p class="small text-secondary mb-3" data-checkout-preview-note>Totals refresh from the server when fulfilment or zone changes. Client totals are never trusted.</p>
                        <button class="btn btn-ss-accent w-100" type="submit" data-checkout-submit>Place order</button>
                        <a class="btn btn-ss-outline w-100 mt-2" href="{{ route('cart.show') }}">Back to cart</a>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
