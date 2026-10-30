@php
    $brand = config('savoury.brand');
@endphp

<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="footer-brand mb-2">Savoury Spot</div>
                <p class="mb-2">{{ $brand['tagline'] }}</p>
                <p class="mb-0">Shorkot City, Punjab, Pakistan</p>
                <p class="mt-2 mb-0">
                    <a href="tel:+923166100080">{{ $brand['phone'] }}</a>
                </p>
                <p class="small mt-3 mb-0 opacity-75">Temporary text brand mark — replace with official logo assets when provided.</p>
            </div>
            <div class="col-6 col-lg-2">
                <h3 class="h6 text-white mb-3">Explore</h3>
                <ul class="list-unstyled d-grid gap-2 mb-0">
                    <li><a href="{{ route('menu.index') }}">Menu</a></li>
                    <li><a href="{{ route('about') }}">About</a></li>
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                    <li><a href="{{ route('orders.track') }}">Track order</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-3">
                <h3 class="h6 text-white mb-3">Policies</h3>
                <ul class="list-unstyled d-grid gap-2 mb-0">
                    <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                    <li><a href="{{ route('terms') }}">Terms and Conditions</a></li>
                    <li><a href="{{ route('delivery-policy') }}">Delivery &amp; Cancellation</a></li>
                </ul>
            </div>
            <div class="col-lg-2">
                <h3 class="h6 text-white mb-3">Order</h3>
                <a class="btn btn-ss-accent" href="{{ route('menu.index') }}">Browse menu</a>
            </div>
        </div>
        <div class="border-top border-light border-opacity-25 mt-4 pt-3 small d-flex flex-column flex-md-row justify-content-between gap-2">
            <span>&copy; {{ now()->year }} Savoury Spot</span>
            <span>Cash on delivery · Cash on pickup</span>
        </div>
    </div>
</footer>
