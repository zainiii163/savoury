@extends('layouts.admin')

@section('title', 'New add-on')

@section('content')
    <h1 class="h3 mb-4">New add-on</h1>
    <div class="info-panel">
        <form method="post" action="{{ route('admin.addons.store') }}">
            @include('admin.addons._form', ['submitLabel' => 'Create add-on'])
        </form>
    </div>
@endsection
