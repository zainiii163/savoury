<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket {{ $order->public_reference }}</title>
    <style>
        body { font-family: Georgia, 'Times New Roman', serif; color: #163039; margin: 1.5rem; }
        h1 { font-size: 1.4rem; margin: 0 0 .25rem; }
        .muted { color: #5c7178; font-size: .9rem; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        th, td { text-align: left; padding: .35rem 0; border-bottom: 1px solid #ddd; font-size: .95rem; }
        .total { font-weight: 700; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <p class="no-print"><button type="button" onclick="window.print()">Print</button></p>

    <h1>Savoury Spot</h1>
    <p class="muted">{{ $brand['tagline'] }} · {{ $brand['phone'] }}</p>

    <p>
        <strong>{{ $order->public_reference }}</strong><br>
        {{ ucfirst($order->fulfillment_type) }} · {{ $presenter->label($order->status, $order->fulfillment_type) }}<br>
        {{ $order->customer_name }} · {{ $order->phone }}
    </p>

    @if ($order->fulfillment_type === 'delivery')
        <p>{{ $order->delivery_zone_name }}<br>{{ $order->address }}</p>
    @endif

    @if ($order->notes)
        <p><strong>Notes:</strong> {{ $order->notes }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>
                        {{ $item->product_name }}
                        @if ($item->variant_name) ({{ $item->variant_name }}) @endif
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>PKR {{ number_format(\App\Support\Money::toMajor((int) $item->line_total_minor)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>
        Subtotal: PKR {{ number_format(\App\Support\Money::toMajor((int) $order->subtotal_minor)) }}<br>
        Delivery: PKR {{ number_format(\App\Support\Money::toMajor((int) $order->delivery_fee_minor)) }}<br>
        <span class="total">Total: PKR {{ number_format(\App\Support\Money::toMajor((int) $order->total_minor)) }}</span><br>
        Payment: {{ $order->payment_status }} ({{ str_replace('_', ' ', $order->payment_method) }})
    </p>
</body>
</html>
