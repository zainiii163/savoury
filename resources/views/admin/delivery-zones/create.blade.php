@extends('layouts.admin')

@section('title', 'New delivery zone')

@section('content')
    <h1 class="h3 mb-4">New delivery zone</h1>
    <form method="post" action="{{ route('admin.delivery-zones.store') }}" class="info-panel col-lg-7">
        @csrf
        @include('admin.delivery-zones._form')
        <button class="btn btn-ss-primary" type="submit">Create zone</button>
        <a class="btn btn-ss-outline" href="{{ route('admin.delivery-zones.index') }}">Cancel</a>
    </form>
@endsection
