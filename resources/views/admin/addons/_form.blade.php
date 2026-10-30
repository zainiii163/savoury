@csrf
<div class="mb-3">
    <label class="form-label" for="name">Name</label>
    <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $addon->name) }}" required>
</div>
<div class="mb-3">
    <label class="form-label" for="slug">Slug</label>
    <input id="slug" name="slug" type="text" class="form-control" value="{{ old('slug', $addon->slug) }}">
</div>
<div class="mb-3">
    <label class="form-label" for="price_pkr">Price (PKR)</label>
    <input id="price_pkr" name="price_pkr" type="number" min="0" step="1" class="form-control" value="{{ old('price_pkr', $pricePkr) }}" required>
</div>
<div class="mb-3">
    <label class="form-label" for="sort_order">Sort order</label>
    <input id="sort_order" name="sort_order" type="number" min="0" class="form-control" value="{{ old('sort_order', $addon->sort_order ?? 0) }}">
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" @checked(old('is_active', $addon->is_active))>
    <label class="form-check-label" for="is_active">Active</label>
</div>
<button class="btn btn-ss-primary" type="submit">{{ $submitLabel }}</button>
