<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Savoury Spot') — Special Aroma, Homemade Trust</title>
    <meta name="description" content="@yield('meta_description', 'Order homemade favourites from Savoury Spot in Shorkot City for delivery or pickup. Cash payments only.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,560;9..144,700&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="visually-hidden-focusable btn btn-ss-primary position-absolute m-2" href="#main-content">Skip to content</a>
    <x-site-header />

    <main id="main-content">
        @yield('content')
    </main>

    <x-site-footer />
    <script>
        (() => {
            const form = document.getElementById('checkout-form');
            if (!form) return;

            const fulfillmentInputs = form.querySelectorAll('input[name="fulfillment_type"]');
            const deliveryFields = form.querySelectorAll('[data-delivery-fields]');
            const payPickup = form.querySelector('#pay_pickup');
            const payDelivery = form.querySelector('#pay_delivery');
            const submit = form.querySelector('[data-checkout-submit]');

            const sync = () => {
                const fulfillment = form.querySelector('input[name="fulfillment_type"]:checked')?.value || 'pickup';
                const isDelivery = fulfillment === 'delivery';
                deliveryFields.forEach((el) => {
                    el.style.display = isDelivery ? '' : 'none';
                });
                if (isDelivery) {
                    payDelivery.checked = true;
                } else {
                    payPickup.checked = true;
                }
            };

            fulfillmentInputs.forEach((input) => input.addEventListener('change', sync));
            sync();

            form.addEventListener('submit', () => {
                if (submit) {
                    submit.disabled = true;
                    submit.textContent = 'Placing order…';
                }
            });
        })();
    </script>
</body>
</html>
