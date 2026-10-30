@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    <h1 class="h3 mb-4">Settings</h1>

    <div class="row g-4">
        <div class="col-lg-6">
            <form method="post" action="{{ route('admin.settings.update') }}" class="info-panel">
                @csrf
                @method('PUT')
                <h2 class="h5 mb-3">Restaurant info</h2>
                <p class="small text-secondary">Leave blank fields empty until the owner supplies final copy. Nothing is invented here.</p>
                <div class="mb-3">
                    <label class="form-label" for="restaurant_name">Display name</label>
                    <input id="restaurant_name" name="restaurant_name" class="form-control" value="{{ old('restaurant_name', $settings['restaurant_name']) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="phone">Phone</label>
                    <input id="phone" name="phone" class="form-control" value="{{ old('phone', $settings['phone']) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="address">Address</label>
                    <textarea id="address" name="address" rows="3" class="form-control">{{ old('address', $settings['address']) }}</textarea>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="ordering_paused" name="ordering_paused" @checked(old('ordering_paused', $settings['ordering_paused']))>
                    <label class="form-check-label" for="ordering_paused">Pause online ordering</label>
                </div>
                <button class="btn btn-ss-primary" type="submit">Save settings</button>
            </form>
        </div>

        <div class="col-lg-6">
            <form method="post" action="{{ route('admin.settings.hours') }}" class="info-panel">
                @csrf
                @method('PUT')
                <h2 class="h5 mb-3">Business hours</h2>
                <p class="small text-secondary">If no hours are saved, checkout treats the restaurant as open. Saving hours enables day/time checks.</p>
                @foreach ($hours as $index => $day)
                    <div class="border rounded p-3 mb-2">
                        <input type="hidden" name="hours[{{ $index }}][day_of_week]" value="{{ $day['day_of_week'] }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>{{ $day['label'] }}</strong>
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" value="1" id="closed_{{ $index }}" name="hours[{{ $index }}][is_closed]" @checked(old("hours.$index.is_closed", $day['is_closed']))>
                                <label class="form-check-label" for="closed_{{ $index }}">Closed</label>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col">
                                <label class="form-label" for="opens_{{ $index }}">Opens</label>
                                <input id="opens_{{ $index }}" type="time" name="hours[{{ $index }}][opens_at]" class="form-control" value="{{ old("hours.$index.opens_at", $day['opens_at']) }}">
                            </div>
                            <div class="col">
                                <label class="form-label" for="closes_{{ $index }}">Closes</label>
                                <input id="closes_{{ $index }}" type="time" name="hours[{{ $index }}][closes_at]" class="form-control" value="{{ old("hours.$index.closes_at", $day['closes_at']) }}">
                            </div>
                        </div>
                    </div>
                @endforeach
                <button class="btn btn-ss-primary" type="submit">Save hours</button>
            </form>
        </div>
    </div>
@endsection
