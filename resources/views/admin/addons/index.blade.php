@extends('layouts.admin')

@section('title', 'Add-ons')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h3 mb-0">Add-ons</h1>
        <a class="btn btn-ss-primary" href="{{ route('admin.addons.create') }}">New add-on</a>
    </div>

    <form class="row g-2 mb-3" method="get">
        <div class="col-md-6">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search add-ons">
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
                    <th>Price</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($addons as $addon)
                    <tr>
                        <td>{{ $addon->name }}</td>
                        <td><x-price :minor="$addon->price_minor" /></td>
                        <td>{{ $addon->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.addons.edit', $addon) }}">Edit</a>
                            <form class="d-inline" method="post" action="{{ route('admin.addons.destroy', $addon) }}" onsubmit="return confirm('Archive this add-on?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-link text-danger p-0 ms-2" type="submit">Archive</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-secondary">No add-ons yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $addons->links() }}</div>
@endsection
