@extends('layouts.app')

@section('title', $activeCategory ? $activeCategory->name.' Menu' : 'Complete Menu')
@section('meta_description', 'Browse the Savoury Spot menu in Shorkot City. Provisional prices pending owner verification.')

@section('content')
    <section class="page-hero">
        <div class="container">
            <x-section-heading
                eyebrow="Menu"
                :title="$activeCategory ? $activeCategory->name : 'Complete menu'"
                text="Live catalogue from MySQL. Images marked placeholder until owner photography is uploaded."
            />
            <div class="notice">
                Provisional prices: Pulao Kabab PKR 375 · Single Plate PKR 565 · Single Without Kabab PKR 465. Editable and pending owner verification.
            </div>

            <form class="row g-2 mt-4" method="get" action="{{ route('menu.index') }}">
                @if ($activeCategory)
                    <input type="hidden" name="category" value="{{ $activeCategory->slug }}">
                @endif
                <div class="col-md-8 col-lg-6">
                    <label class="visually-hidden" for="menu-search">Search menu</label>
                    <input
                        id="menu-search"
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        class="form-control form-control-lg"
                        placeholder="Search dishes"
                    >
                </div>
                <div class="col-auto">
                    <button class="btn btn-ss-primary" type="submit">Search</button>
                </div>
            </form>
        </div>
    </section>

    <section class="section pt-0">
        <div class="container">
            <div class="menu-toolbar" role="navigation" aria-label="Menu categories">
                <a class="category-chip {{ $activeCategory === null ? 'active' : '' }}" href="{{ route('menu.index', array_filter(['q' => $search ?: null])) }}">All dishes</a>
                @foreach ($categories as $category)
                    <a
                        class="category-chip {{ $activeCategory?->id === $category->id ? 'active' : '' }}"
                        href="{{ route('menu.index', array_filter(['category' => $category->slug, 'q' => $search ?: null])) }}"
                    >
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>

            @if ($products->isEmpty())
                <div class="policy-card">
                    <p class="mb-0">No available products in this category yet.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($products as $product)
                        <div class="col-md-6 col-xl-4">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
