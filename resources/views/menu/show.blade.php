@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', $product->description ?: ($product->name.' from Savoury Spot, Shorkot City.'))

@section('content')
    <section class="section">
        <div class="container">
            <nav aria-label="Breadcrumb" class="mb-4">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('menu.index') }}">Menu</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>

            <div class="row g-4 g-xl-5 align-items-start">
                <div class="col-lg-6">
                    <div class="product-detail-media">
                        @if ($product->image_path)
                            <img src="{{ filter_var($product->image_path, FILTER_VALIDATE_URL) ? $product->image_path : asset($product->image_path) }}" alt="{{ $product->name }}">
                        @else
                            <span aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    @unless ($product->image_path)
                        <p class="small text-secondary mt-2 mb-0">Placeholder visual — official product photography not supplied yet.</p>
                    @endunless
                </div>
                <div class="col-lg-6">
                    <p class="section-heading__eyebrow mb-2">{{ $product->category?->name ?? 'Menu' }}</p>
                    <h1 class="display-font mb-3">{{ $product->name }}</h1>
                    <div class="mb-3">
                        <x-price class="fs-3" :minor="$product->price_minor" />
                    </div>
                    <p class="text-secondary mb-4">
                        {{ $product->description ?: 'Description pending owner-approved copy. Price is provisional and editable.' }}
                    </p>

                    @if ($product->is_available)
                        <form method="post" action="{{ route('cart.items.store') }}" class="info-panel mb-4">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            @if ($product->variants->isNotEmpty())
                                <div class="mb-3">
                                    <label class="form-label" for="product_variant_id">Option</label>
                                    <select id="product_variant_id" name="product_variant_id" class="form-select" required>
                                        @foreach ($product->variants as $variant)
                                            <option value="{{ $variant->id }}" @selected($variant->is_default)>
                                                {{ $variant->name }} — PKR {{ \App\Support\Money::toMajor((int) $variant->price_minor) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if ($product->addons->isNotEmpty())
                                <div class="mb-3">
                                    <div class="form-label">Add-ons</div>
                                    @foreach ($product->addons as $addon)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="addon_ids[]" value="{{ $addon->id }}" id="addon_{{ $addon->id }}">
                                            <label class="form-check-label" for="addon_{{ $addon->id }}">
                                                {{ $addon->name }} (+ <x-price :minor="$addon->price_minor" />)
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mb-3" style="max-width: 8rem;">
                                <label class="form-label" for="quantity">Quantity</label>
                                <input id="quantity" type="number" name="quantity" min="1" max="50" value="1" class="form-control" required>
                            </div>

                            <button class="btn btn-ss-accent" type="submit">Add to cart</button>
                        </form>
                    @else
                        @if ($product->variants->isNotEmpty())
                            <div class="info-panel mb-3">
                                <h2 class="h5 mb-3">Options</h2>
                                <ul class="list-unstyled mb-0 d-grid gap-2">
                                    @foreach ($product->variants as $variant)
                                        <li class="d-flex justify-content-between gap-3">
                                            <span>{{ $variant->name }}</span>
                                            <x-price :minor="$variant->price_minor" />
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="notice mb-4">This item is currently unavailable.</div>
                    @endif

                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-ss-outline" href="{{ route('menu.index') }}">Back to menu</a>
                        <a class="btn btn-ss-outline" href="tel:+923166100080">Call {{ $brand['phone'] }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
