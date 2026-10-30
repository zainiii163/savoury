@props([
    'product',
])

@php
    $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 1));
@endphp

<article class="product-card">
    <a href="{{ route('menu.show', $product->slug) }}" class="text-decoration-none text-reset">
        <div class="product-card__media">
            @if ($product->image_path)
                <img src="{{ filter_var($product->image_path, FILTER_VALIDATE_URL) ? $product->image_path : asset($product->image_path) }}" alt="{{ $product->name }}">
            @else
                <span aria-hidden="true">{{ $initial }}</span>
                <span class="product-card__badge">Placeholder image</span>
            @endif
        </div>
        <div class="product-card__body">
            <h3 class="product-card__name">{{ $product->name }}</h3>
            <p class="product-card__meta mb-2">
                {{ $product->category?->name ?? 'Menu' }}
                @unless ($product->is_available)
                    · Currently unavailable
                @endunless
            </p>
            <div class="product-card__row">
                <x-price :minor="$product->price_minor" />
                <span class="btn btn-ss-outline btn-sm">View / add</span>
            </div>
        </div>
    </a>
</article>
