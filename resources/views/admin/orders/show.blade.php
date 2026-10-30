@extends('layouts.admin')

@section('title', $order->public_reference)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="section-heading__eyebrow mb-1">Order</p>
            <h1 class="h3 mb-1">{{ $order->public_reference }}</h1>
            <p class="text-secondary mb-0">
                {{ $presenter->label($order->status, $order->fulfillment_type) }}
                · {{ $order->payment_status }}
                · {{ ucfirst($order->fulfillment_type) }}
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-ss-outline" href="{{ route('admin.orders.ticket', $order) }}" target="_blank" rel="noopener">Print ticket</a>
            <a class="btn btn-ss-outline" href="{{ route('admin.orders.index') }}">Back to inbox</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="info-panel mb-4">
                <h2 class="h5 mb-3">Customer</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8">{{ $order->customer_name }}</dd>
                    <dt class="col-sm-4">Phone</dt>
                    <dd class="col-sm-8"><a href="tel:{{ preg_replace('/\D+/', '', $order->phone) }}">{{ $order->phone }}</a></dd>
                    @if ($order->fulfillment_type === 'delivery')
                        <dt class="col-sm-4">Zone</dt>
                        <dd class="col-sm-8">{{ $order->delivery_zone_name ?: '—' }}</dd>
                        <dt class="col-sm-4">Address</dt>
                        <dd class="col-sm-8">{{ $order->address }}</dd>
                    @endif
                    @if ($order->notes)
                        <dt class="col-sm-4">Notes</dt>
                        <dd class="col-sm-8">{{ $order->notes }}</dd>
                    @endif
                </dl>
            </div>

            <div class="info-panel mb-4">
                <h2 class="h5 mb-3">Items</h2>
                <ul class="list-unstyled mb-3">
                    @foreach ($order->items as $item)
                        <li class="d-flex justify-content-between gap-3 mb-2">
                            <span>
                                {{ $item->quantity }} × {{ $item->product_name }}
                                @if ($item->variant_name) ({{ $item->variant_name }}) @endif
                            </span>
                            <x-price :minor="$item->line_total_minor" />
                        </li>
                    @endforeach
                </ul>
                <div class="d-flex justify-content-between mb-1"><span>Subtotal</span><x-price :minor="$order->subtotal_minor" /></div>
                <div class="d-flex justify-content-between mb-1"><span>Delivery fee</span><x-price :minor="$order->delivery_fee_minor" /></div>
                <div class="d-flex justify-content-between fw-bold"><span>Total</span><x-price :minor="$order->total_minor" /></div>
            </div>

            <div class="info-panel">
                <h2 class="h5 mb-3">Status history</h2>
                <ul class="list-unstyled mb-0 d-grid gap-2">
                    @forelse ($order->statusHistories as $history)
                        <li class="small">
                            <strong>{{ $presenter->label($history->to_status, $order->fulfillment_type) }}</strong>
                            @if ($history->from_status)
                                <span class="text-secondary">from {{ $presenter->label($history->from_status, $order->fulfillment_type) }}</span>
                            @endif
                            · {{ $history->created_at?->timezone(config('savoury.display_timezone'))->format('d M, h:i A') }}
                            @if ($history->actor) · {{ $history->actor->name }} @endif
                            @if ($history->reason)
                                <div class="text-secondary">{{ $history->reason }}</div>
                            @endif
                        </li>
                    @empty
                        <li class="text-secondary">No history yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="info-panel mb-4">
                <h2 class="h5 mb-3">Update status</h2>
                @if ($allowedStatuses === [])
                    <p class="text-secondary mb-0">No further transitions available.</p>
                @else
                    <form method="post" action="{{ route('admin.orders.transition', $order) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="status">Next status</label>
                            <select id="status" name="status" class="form-select" required>
                                @foreach ($allowedStatuses as $status)
                                    <option value="{{ $status }}">{{ $presenter->label($status, $order->fulfillment_type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="reason">Reason (optional)</label>
                            <input id="reason" type="text" name="reason" class="form-control" maxlength="500">
                        </div>
                        <button class="btn btn-ss-primary" type="submit">Apply status</button>
                    </form>
                @endif
            </div>

            <div class="info-panel mb-4">
                <h2 class="h5 mb-3">Cash collection</h2>
                @if ($order->payment_status === 'paid')
                    <p class="mb-0 text-secondary">Marked paid
                        @php $paid = $order->payments->firstWhere('status', 'paid'); @endphp
                        @if ($paid?->paid_at)
                            at {{ $paid->paid_at->timezone(config('savoury.display_timezone'))->format('d M, h:i A') }}
                        @endif
                        @if ($paid?->collector)
                            by {{ $paid->collector->name }}
                        @endif
                    </p>
                @elseif (auth()->user()?->canCollectCash())
                    <form method="post" action="{{ route('admin.orders.collect', $order) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="notes">Notes (optional)</label>
                            <input id="notes" type="text" name="notes" class="form-control">
                        </div>
                        <button class="btn btn-ss-accent" type="submit">Mark cash collected</button>
                    </form>
                @else
                    <p class="mb-0 text-secondary">Unpaid — you do not have cash collection permission.</p>
                @endif
            </div>

            <div class="info-panel">
                <h2 class="h5 mb-3">Timeline</h2>
                <ol class="order-timeline list-unstyled mb-0">
                    @foreach ($timeline as $step)
                        <li class="order-timeline__step order-timeline__step--{{ $step['state'] }}">
                            <span class="order-timeline__marker" aria-hidden="true"></span>
                            <span class="order-timeline__label">{{ $step['label'] }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
@endsection
