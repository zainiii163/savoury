@php
    /** @var \App\Models\DeliveryZone|null $zone */
    $zone = $zone ?? null;
@endphp

<div class="mb-3">
    <label class="form-label" for="name">Name</label>
    <input id="name" name="name" class="form-control" value="{{ old('name', $zone?->name) }}" required>
</div>
<div class="mb-3">
    <label class="form-label" for="slug">Slug (optional)</label>
    <input id="slug" name="slug" class="form-control" value="{{ old('slug', $zone?->slug) }}">
</div>
<div class="row g-3">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="delivery_fee_pkr">Delivery fee (PKR)</label>
        <input id="delivery_fee_pkr" name="delivery_fee_pkr" type="number" min="0" step="1" class="form-control"
               value="{{ old('delivery_fee_pkr', $zone ? \App\Support\Money::toMajor((int) $zone->delivery_fee_minor) : 0) }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="min_order_pkr">Minimum order (PKR)</label>
        <input id="min_order_pkr" name="min_order_pkr" type="number" min="0" step="1" class="form-control"
               value="{{ old('min_order_pkr', $zone ? \App\Support\Money::toMajor((int) $zone->min_order_minor) : 0) }}">
    </div>
</div>
<div class="mb-3">
    <label class="form-label" for="coverage_notes">Coverage notes</label>
    <textarea id="coverage_notes" name="coverage_notes" rows="3" class="form-control">{{ old('coverage_notes', $zone?->coverage_notes) }}</textarea>
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" @checked(old('is_active', $zone?->is_active ?? true))>
    <label class="form-check-label" for="is_active">Active</label>
</div>
