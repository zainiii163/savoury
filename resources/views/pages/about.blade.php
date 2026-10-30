@extends('layouts.app')

@section('title', 'About Us')

@section('content')
    <section class="page-hero">
        <div class="container">
            <x-section-heading
                eyebrow="About"
                title="Special aroma, homemade trust"
                text="Savoury Spot is a single-branch restaurant serving Shorkot City with delivery and pickup."
            />
        </div>
    </section>
    <section class="section pt-0">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="info-panel">
                        <p>We are building an original ordering experience around hospitality, clarity, and cash-friendly checkout.</p>
                        <p class="mb-0">Detailed story, team notes, and kitchen photography will replace this temporary copy when the owner supplies brand assets and approved text.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="info-panel">
                        <h2 class="h5">Visit &amp; call</h2>
                        <p class="mb-2"><strong>Placeholder address:</strong> full street address pending owner confirmation.</p>
                        <p class="mb-0"><a href="tel:+923166100080">{{ $brand['phone'] }}</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
