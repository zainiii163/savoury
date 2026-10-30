@extends('layouts.admin')

@section('title', 'Delivery zones')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Delivery zones</h1>
            <p class="text-secondary mb-0">No zones are seeded. Delivery checkout stays unavailable until you add one.</p>
        </div>
        <a class="btn btn-ss-primary" href="{{ route('admin.delivery-zones.create') }}">New zone</a>
    </div>

    <div class="table-responsive info-panel p-0">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Fee</th>
                    <th>Min order</th>
                    <th>Active</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($zones as $zone)
                    <tr>
                        <td>
                            <strong>{{ $zone->name }}</strong>
                            @if ($zone->coverage_notes)
                                <div class="small text-secondary">{{ $zone->coverage_notes }}</div>
                            @endif
                        </td>
                        <td><x-price :minor="$zone->delivery_fee_minor" /></td>
                        <td><x-price :minor="$zone->min_order_minor" /></td>
                        <td>{{ $zone->is_active ? 'Yes' : 'No' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.delivery-zones.edit', $zone) }}">Edit</a>
                            <form class="d-inline" method="post" action="{{ route('admin.delivery-zones.destroy', $zone) }}" onsubmit="return confirm('Archive this zone?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-link text-danger p-0 ms-2" type="submit">Archive</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-secondary p-4">No delivery zones configured.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $zones->links() }}</div>
@endsection
