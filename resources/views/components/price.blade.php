@props([
    'minor' => 0,
])

@php
    $amount = \App\Support\Money::toMajor((int) $minor);
    $formatted = is_int($amount)
        ? number_format($amount)
        : number_format((float) $amount, 2);
@endphp

<span {{ $attributes->merge(['class' => 'price']) }}>PKR {{ $formatted }}</span>
