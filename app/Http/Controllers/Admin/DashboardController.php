<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardMetricsService;
use App\Services\Orders\OrderStatusPresenter;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        DashboardMetricsService $metrics,
        OrderStatusPresenter $presenter,
    ): View {
        return view('admin.dashboard', [
            'metrics' => $metrics->summarize(),
            'presenter' => $presenter,
            'brand' => config('savoury.brand'),
        ]);
    }
}
