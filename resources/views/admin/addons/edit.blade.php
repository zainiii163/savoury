@extends('layouts.admin')

@section('title', 'Edit add-on')

@section('content')
    <h1 class="h3 mb-4">Edit add-on</h1>
    <div class="info-panel">
        <form method="post" action="{{ route('admin.addons.update', $addon) }}">
            @method('PUT')
            @include('admin.addons._form', ['submitLabel' => 'Save add-on'])
        </form>
    </div>
@endsection
