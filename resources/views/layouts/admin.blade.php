<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Savoury Spot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,700&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header border-bottom">
        <div class="container py-3 d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <a class="brand-mark" href="{{ route('admin.dashboard') }}">
                <span class="brand-mark__badge" aria-hidden="true">SS</span>
                <span class="brand-mark__text">
                    <span class="brand-mark__name">Savoury Admin</span>
                    <span class="brand-mark__tag">Operations</span>
                </span>
            </a>
            <nav class="d-flex flex-wrap gap-2 align-items-center">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
                @if (auth()->user()?->canManageOrders())
                    <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">Orders</a>
                @endif
                @if (auth()->user()?->canManageMenu())
                    <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">Products</a>
                    <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">Categories</a>
                    <a class="nav-link {{ request()->routeIs('admin.addons.*') ? 'active' : '' }}" href="{{ route('admin.addons.index') }}">Add-ons</a>
                @endif
                @if (auth()->user()?->canManageSettings())
                    <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">Settings</a>
                    <a class="nav-link {{ request()->routeIs('admin.delivery-zones.*') ? 'active' : '' }}" href="{{ route('admin.delivery-zones.index') }}">Zones</a>
                @endif
                @if (auth()->user()?->canViewReports())
                    <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">Reports</a>
                @endif
                <a class="nav-link" href="{{ route('menu.index') }}" target="_blank" rel="noopener">View storefront</a>
                <form method="post" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="btn btn-ss-outline btn-sm" type="submit">Log out</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="container py-4">
        @if (session('status'))
            <div class="notice mb-3">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
