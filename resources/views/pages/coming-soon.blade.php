@extends('layouts.app')

@section('title', $title)

@section('content')
    <section class="section">
        <div class="container col-lg-8">
            <div class="info-panel text-center p-5">
                <p class="section-heading__eyebrow mb-2">Coming soon</p>
                <h1 class="display-font mb-3">{{ $title }}</h1>
                <p class="text-secondary mb-4">{{ $message }}</p>
                <a class="btn btn-ss-primary" href="{{ route('menu.index') }}">Browse menu</a>
            </div>
        </div>
    </section>
@endsection
