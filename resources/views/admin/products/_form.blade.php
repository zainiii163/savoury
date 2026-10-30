@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">Name</label>
        <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $product->name) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="slug">Slug</label>
        <input id="slug" name="slug" type="text" class="form-control" value="{{ old('slug', $product->slug) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="category_id">Category</label>
        <select id="category_id" name="category_id" class="form-select" required>
            <option value="">Select category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="price_pkr">Price (PKR)</label>
        <input id="price_pkr" name="price_pkr" type="number" min="0" step="1" class="form-control" value="{{ old('price_pkr', $pricePkr) }}" required>
    </div>
    <div class="col-12">
        <label class="form-label" for="description">Description</label>
        <textarea id="description" name="description" rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="image">Product image</label>
        <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp" class="form-control">
        <div class="form-text">JPG, PNG, or WebP up to 2MB.</div>
        @if ($product->image_path)
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" value="1" id="remove_image" name="remove_image">
                <label class="form-check-label" for="remove_image">Remove current image</label>
            </div>
            <p class="small mt-2 mb-0">Current: <code>{{ $product->image_path }}</code></p>
        @endif
    </div>
    <div class="col-md-6">
        <label class="form-label" for="sort_order">Sort order</label>
        <input id="sort_order" name="sort_order" type="number" min="0" class="form-control" value="{{ old('sort_order', $product->sort_order ?? 0) }}">
        <div class="d-flex flex-wrap gap-3 mt-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" @checked(old('is_active', $product->is_active))>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="is_available" name="is_available" @checked(old('is_available', $product->is_available))>
                <label class="form-check-label" for="is_available">Available</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="is_featured" name="is_featured" @checked(old('is_featured', $product->is_featured))>
                <label class="form-check-label" for="is_featured">Featured</label>
            </div>
        </div>
    </div>
</div>

<hr class="my-4">

<h2 class="h5">Variants</h2>
<p class="text-secondary small">Leave name blank to skip a row. Prices are PKR major units.</p>
@foreach (old('variants', $variantRows) as $index => $variant)
    <div class="row g-2 mb-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label" for="variant_name_{{ $index }}">Name</label>
            <input id="variant_name_{{ $index }}" name="variants[{{ $index }}][name]" type="text" class="form-control" value="{{ $variant['name'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="variant_price_{{ $index }}">Price (PKR)</label>
            <input id="variant_price_{{ $index }}" name="variants[{{ $index }}][price_pkr]" type="number" min="0" step="1" class="form-control" value="{{ $variant['price_pkr'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="variant_default_{{ $index }}" name="variants[{{ $index }}][is_default]" @checked(!empty($variant['is_default']))>
                <label class="form-check-label" for="variant_default_{{ $index }}">Default</label>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="variant_available_{{ $index }}" name="variants[{{ $index }}][is_available]" @checked(($variant['is_available'] ?? true))>
                <label class="form-check-label" for="variant_available_{{ $index }}">Available</label>
            </div>
        </div>
    </div>
@endforeach

<hr class="my-4">

<h2 class="h5">Add-ons</h2>
@if ($addons->isEmpty())
    <p class="text-secondary">No add-ons yet. Create some under Add-ons first.</p>
@else
    <div class="row g-2">
        @foreach ($addons as $addon)
            <div class="col-md-4">
                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="addon_ids[]"
                        value="{{ $addon->id }}"
                        id="addon_{{ $addon->id }}"
                        @checked(in_array($addon->id, old('addon_ids', $selectedAddonIds), true))
                    >
                    <label class="form-check-label" for="addon_{{ $addon->id }}">
                        {{ $addon->name }}
                        (<span class="text-secondary">(<x-price :minor="$addon->price_minor" />)</span>
                    </label>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div class="mt-4">
    <button class="btn btn-ss-primary" type="submit">{{ $submitLabel }}</button>
</div>
