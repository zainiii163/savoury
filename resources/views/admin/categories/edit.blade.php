@extends('layouts.admin')

@section('title', 'Edit category')

@section('content')
    <h1 class="h3 mb-4">Edit category</h1>
    <div class="info-panel">
        <form method="post" action="{{ route('admin.categories.update', $category) }}">
            @method('PUT')
            @include('admin.categories._form', ['submitLabel' => 'Save category'])
        </form>
    </div>
@endsection
