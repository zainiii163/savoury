@extends('layouts.admin')

@section('title', 'New category')

@section('content')
    <h1 class="h3 mb-4">New category</h1>
    <div class="info-panel">
        <form method="post" action="{{ route('admin.categories.store') }}">
            @include('admin.categories._form', ['submitLabel' => 'Create category'])
        </form>
    </div>
@endsection
