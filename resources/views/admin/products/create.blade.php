@extends('layouts.admin')

@section('title', 'New product')

@section('content')
    <h1 class="h3 mb-4">New product</h1>
    <div class="info-panel">
        <form method="post" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @include('admin.products._form', ['submitLabel' => 'Create product'])
        </form>
    </div>
@endsection
