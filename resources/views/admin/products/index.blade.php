@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h3 mb-0">Products</h1>
        <a class="btn btn-ss-primary" href="{{ route('admin.products.create') }}">New product</a>
    </div>

    <form class="row g-2 mb-3" method="get">
        <div class="col-md-4">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search products">
        </div>
        <div class="col-md-4">
            <select name="category_id" class="form-select">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-ss-outline" type="submit">Filter</button>
        </div>
    </form>

    <div class="table-responsive info-panel p-0">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Flags</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>
                            <strong>{{ $product->name }}</strong>
                            <div class="small text-secondary">{{ $product->slug }}</div>
                        </td>
                        <td>{{ $product->category?->name }}</td>
                        <td><x-price :minor="$product->price_minor" /></td>
                        <td class="small">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                            · {{ $product->is_available ? 'Available' : 'Unavailable' }}
                            @if ($product->is_featured) · Featured @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.products.edit', $product) }}">Edit</a>
                            <form class="d-inline" method="post" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Archive this product?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-link text-danger p-0 ms-2" type="submit">Archive</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-secondary">No products yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $products->links() }}</div>
@endsection
