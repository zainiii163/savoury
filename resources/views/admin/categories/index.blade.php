@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h3 mb-0">Categories</h1>
        <a class="btn btn-ss-primary" href="{{ route('admin.categories.create') }}">New category</a>
    </div>

    <form class="row g-2 mb-3" method="get">
        <div class="col-md-6">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search categories">
        </div>
        <div class="col-auto">
            <button class="btn btn-ss-outline" type="submit">Search</button>
        </div>
    </form>

    <div class="table-responsive info-panel p-0">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td>{{ $category->products_count }}</td>
                        <td>{{ $category->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                            <form class="d-inline" method="post" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Deactivate or delete this category?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-link text-danger p-0 ms-2" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-secondary">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $categories->links() }}</div>
@endsection
