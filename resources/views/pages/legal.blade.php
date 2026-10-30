@extends('layouts.app')

@section('title', $title)

@section('content')
    <section class="page-hero">
        <div class="container">
            <x-section-heading eyebrow="Policies" :title="$title" :text="$intro" />
            <div class="notice">
                Placeholder policy page — final legal wording must be provided by the business owner before launch.
            </div>
        </div>
    </section>
@endsection
