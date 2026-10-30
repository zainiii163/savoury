@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
    <section class="page-hero">
        <div class="container">
            <x-section-heading
                eyebrow="Contact"
                title="Talk to Savoury Spot"
                text="Reach us by phone today. Online contact forms can be added once owner workflows are confirmed."
            />
        </div>
    </section>
    <section class="section pt-0">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="info-panel">
                        <h2 class="h5">Phone</h2>
                        <p class="mb-3"><a class="fs-4 text-decoration-none" href="tel:+923166100080">{{ $brand['phone'] }}</a></p>
                        <h2 class="h5">Location</h2>
                        <p class="mb-0">Shorkot City, Punjab, Pakistan</p>
                        <p class="small text-secondary mt-2 mb-0">Map pin and complete address are placeholders until supplied by the owner.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-panel">
                        <h2 class="h5">Ordering</h2>
                        <ul class="mb-0">
                            <li>Home delivery</li>
                            <li>Restaurant pickup</li>
                            <li>Cash on delivery / cash on pickup</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
