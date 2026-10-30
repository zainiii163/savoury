@extends('layouts.admin')

@section('title', 'Edit '.$zone->name)

@section('content')
    <h1 class="h3 mb-4">Edit {{ $zone->name }}</h1>
    <form method="post" action="{{ route('admin.delivery-zones.update', $zone) }}" class="info-panel col-lg-7">
        @csrf
        @method('PUT')
        @include('admin.delivery-zones._form', ['zone' => $zone])
        <button class="btn btn-ss-primary" type="submit">Save zone</button>
        <a class="btn btn-ss-outline" href="{{ route('admin.delivery-zones.index') }}">Cancel</a>
    </form>
@endsection
