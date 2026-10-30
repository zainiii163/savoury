@props([
    'eyebrow' => null,
    'title',
    'text' => null,
])

<div {{ $attributes->merge(['class' => 'section-heading']) }}>
    @if ($eyebrow)
        <div class="section-heading__eyebrow">{{ $eyebrow }}</div>
    @endif
    <h2 class="section-heading__title">{{ $title }}</h2>
    @if ($text)
        <p class="section-heading__text">{{ $text }}</p>
    @endif
</div>
