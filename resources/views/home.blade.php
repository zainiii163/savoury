@extends('layouts.app')

@section('title', 'Home')
@section('meta_description', 'Savoury Spot in Shorkot City — homemade food for delivery or pickup. Cash on delivery and cash on pickup.')

@section('content')
    <section class="hero">
        <div class="container hero__content">
            <div class="hero__eyebrow">Shorkot City · Delivery &amp; Pickup</div>
            <h1 class="hero__title">Savoury Spot</h1>
            <p class="hero__lead">
                {{ $brand['tagline'] }}. Order from our provisional homemade menu for restaurant pickup or home delivery — cash payments only.
            </p>
            <div class="hero__actions">
                <a class="btn btn-ss-accent" href="{{ route('menu.index') }}">Order now</a>
                <a class="btn btn-ss-outline" href="{{ route('contact') }}" style="border-color: rgba(255,255,255,.45); color: #fff;">Call {{ $brand['phone'] }}</a>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <x-section-heading
                eyebrow="Signature dishes"
                :title="$showingFeaturedOnly ? 'Featured favourites' : 'Provisional menu highlights'"
                :text="$showingFeaturedOnly
                    ? 'Hand-picked dishes from the live catalogue.'
                    : 'Featured selection awaits owner confirmation. Showing the provisional seeded menu for now.'"
            />

            <div class="notice mb-4">
                Prices are provisional and editable. Owner verification is required before production launch.
            </div>

            @if ($signatureDishes->isEmpty())
                <div class="policy-card">
                    <p class="mb-0">No active menu items are available yet. Seed the provisional catalogue to preview dishes here.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($signatureDishes as $product)
                        <div class="col-md-6 col-xl-4">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="section section-muted">
        <div class="container">
            <x-section-heading
                eyebrow="Categories"
                title="Popular menu categories"
                text="Browse by category. More categories can be added once the owner expands the catalogue."
            />

            <div class="d-flex flex-wrap gap-3">
                @forelse ($categories as $category)
                    <a class="category-chip" href="{{ route('menu.index', ['category' => $category->slug]) }}">
                        {{ $category->name }}
                        <span class="opacity-75">({{ $category->visible_products_count }})</span>
                    </a>
                @empty
                    <div class="policy-card">No active categories yet.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-6">
                    <x-section-heading
                        eyebrow="Our kitchen"
                        title="Homemade trust, carefully prepared"
                        text="Savoury Spot serves Shorkot City with a focus on aroma, hospitality, and reliable cash-based ordering."
                    />
                    <div class="info-panel">
                        <p class="mb-3">We keep the experience simple: choose delivery or pickup, pay in cash, and track your order once checkout is live.</p>
                        <a class="btn btn-ss-primary" href="{{ route('about') }}">About Savoury Spot</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-panel">
                                <h3 class="h5 mb-2">Delivery &amp; pickup</h3>
                                <p class="mb-0 text-secondary">Both fulfilment options are supported. Delivery zones and fees are <strong>not invented</strong> — they will appear after owner confirmation.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-panel">
                                <h3 class="h5 mb-2">Payments</h3>
                                <p class="mb-0 text-secondary">Cash on delivery and cash on pickup only. No online payment gateway in the MVP.</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-panel">
                                <h3 class="h5 mb-2">Hours &amp; location</h3>
                                <p class="mb-2 text-secondary"><strong>Placeholder:</strong> opening hours and full street address await owner-supplied details.</p>
                                <p class="mb-0">Phone: <a href="tel:+923166100080">{{ $brand['phone'] }}</a> · Shorkot City, Punjab</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section section-muted">
        <div class="container">
            <div class="info-panel d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 mb-2">Ready to order?</h2>
                    <p class="mb-0 text-secondary">Browse the live MySQL-backed menu and call us anytime at {{ $brand['phone'] }}.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-ss-accent" href="{{ route('menu.index') }}">View full menu</a>
                    <a class="btn btn-ss-outline" href="{{ route('contact') }}">Contact</a>
                </div>
            </div>
        </div>
    </section>
@endsection
