@php
    $brand = config('savoury.brand');
    $cartCount = $cartCount ?? 0;
@endphp

<header class="site-header sticky-top">
    <nav class="navbar navbar-expand-lg container py-3" aria-label="Primary">
        <a class="brand-mark" href="{{ route('home') }}">
            <span class="brand-mark__badge" aria-hidden="true">SS</span>
            <span class="brand-mark__text">
                <span class="brand-mark__name">Savoury Spot</span>
                <span class="brand-mark__tag">{{ $brand['tagline'] }}</span>
            </span>
        </a>

        <button
            class="navbar-toggler border-0 shadow-none"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#primaryNav"
            aria-controls="primaryNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="primaryNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('menu.*') ? 'active' : '' }}" href="{{ route('menu.index') }}">Menu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('cart.*') ? 'active' : '' }}" href="{{ route('cart.show') }}">
                        Cart @if($cartCount > 0)<span class="badge text-bg-warning">{{ $cartCount }}</span>@endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('orders.track') ? 'active' : '' }}" href="{{ route('orders.track') }}">Track order</a>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-ss-primary" href="{{ route('menu.index') }}">Order now</a>
                </li>
            </ul>
        </div>
    </nav>
</header>
