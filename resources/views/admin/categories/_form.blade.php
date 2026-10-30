@csrf
<div class="mb-3">
    <label class="form-label" for="name">Name</label>
    <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $category->name) }}" required>
</div>
<div class="mb-3">
    <label class="form-label" for="slug">Slug</label>
    <input id="slug" name="slug" type="text" class="form-control" value="{{ old('slug', $category->slug) }}">
</div>
<div class="mb-3">
    <label class="form-label" for="description">Description</label>
    <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $category->description) }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label" for="sort_order">Sort order</label>
    <input id="sort_order" name="sort_order" type="number" min="0" class="form-control" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
</div>
<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" @checked(old('is_active', $category->is_active))>
    <label class="form-check-label" for="is_active">Active</label>
</div>
<button class="btn btn-ss-primary" type="submit">{{ $submitLabel }}</button>
