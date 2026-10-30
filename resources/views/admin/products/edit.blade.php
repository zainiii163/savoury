@extends('layouts.admin')

@section('title', 'Edit product')

@section('content')
    <h1 class="h3 mb-4">Edit product</h1>
    <div class="info-panel">
        <form method="post" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.products._form', ['submitLabel' => 'Save product'])
        </form>
    </div>
@endsection
