@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Reports</h1>
            <p class="text-secondary mb-0">
                {{ $report['from']->format('d M Y H:i') }} — {{ $report['to']->format('d M Y H:i') }}
                ({{ config('savoury.display_timezone') }})
            </p>
        </div>
        <form method="get" class="d-flex flex-wrap gap-2 align-items-end">
            <div>
                <label class="form-label mb-1" for="period">Period</label>
                <select id="period" name="period" class="form-select">
                    <option value="today" @selected($period === 'today')>Today</option>
                    <option value="week" @selected($period === 'week')>This week</option>
                    <option value="month" @selected($period === 'month')>This month</option>
                    <option value="custom" @selected($period === 'custom')>Custom</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1" for="from">From</label>
                <input id="from" type="date" name="from" class="form-control" value="{{ request('from', $report['from']->toDateString()) }}">
            </div>
            <div>
                <label class="form-label mb-1" for="to">To</label>
                <input id="to" type="date" name="to" class="form-control" value="{{ request('to', $report['to']->toDateString()) }}">
            </div>
            <button class="btn btn-ss-outline" type="submit">Apply</button>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="info-panel">
                <div class="text-secondary small">Orders</div>
                <div class="fs-3 fw-bold">{{ $report['order_count'] }}</div>
                <div class="small text-secondary">{{ $report['cancelled_count'] }} cancelled/rejected</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-panel">
                <div class="text-secondary small">Sales</div>
                <div class="fs-3 fw-bold"><x-price :minor="$report['sales_minor']" /></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-panel">
                <div class="text-secondary small">Cash collected</div>
                <div class="fs-3 fw-bold"><x-price :minor="$report['cash']['paid_minor']" /></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-panel">
                <div class="text-secondary small">Cash outstanding</div>
                <div class="fs-3 fw-bold"><x-price :minor="$report['cash']['unpaid_minor']" /></div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="info-panel">
                <h2 class="h5 mb-3">Delivery vs pickup</h2>
                <ul class="list-unstyled mb-0 d-grid gap-2">
                    <li class="d-flex justify-content-between gap-3">
                        <span>Pickup · {{ $report['fulfillment']['pickup']['count'] }}</span>
                        <x-price :minor="$report['fulfillment']['pickup']['sales_minor']" />
                    </li>
                    <li class="d-flex justify-content-between gap-3">
                        <span>Delivery · {{ $report['fulfillment']['delivery']['count'] }}</span>
                        <x-price :minor="$report['fulfillment']['delivery']['sales_minor']" />
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="info-panel p-0 overflow-hidden">
                <div class="p-3 border-bottom"><h2 class="h5 mb-0">Product performance</h2></div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['products'] as $row)
                                <tr>
                                    <td>{{ $row->product_name }}</td>
                                    <td>{{ $row->qty }}</td>
                                    <td><x-price :minor="$row->sales_minor" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-secondary p-4">No sold items in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
