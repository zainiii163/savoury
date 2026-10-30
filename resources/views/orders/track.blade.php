@extends('layouts.app')

@section('title', 'Track order')

@section('content')
    <section class="page-hero">
        <div class="container col-lg-7">
            <x-section-heading
                eyebrow="Tracking"
                title="Follow your order"
                text="Use the order reference and the tracking token from your confirmation. The token is never stored in plaintext."
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
        <div class="container col-lg-7">
            <form method="post" action="{{ route('orders.track.store') }}" class="info-panel">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="public_reference">Order reference</label>
                    <input
                        id="public_reference"
                        name="public_reference"
                        type="text"
                        class="form-control"
                        value="{{ old('public_reference', $reference) }}"
                        placeholder="SS-YYMMDD-XXXXXX"
                        autocomplete="off"
                        required
                    >
                </div>
                <div class="mb-4">
                    <label class="form-label" for="tracking_token">Tracking token</label>
                    <input
                        id="tracking_token"
                        name="tracking_token"
                        type="text"
                        class="form-control"
                        value="{{ old('tracking_token') }}"
                        autocomplete="off"
                        required
                    >
                </div>
                <button class="btn btn-ss-accent" type="submit">View order status</button>
            </form>
        </div>
    </section>
@endsection
