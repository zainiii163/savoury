<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(Request $request, ReportService $reports): View
    {
        $timezone = config('savoury.display_timezone');
        $period = $request->string('period', 'today')->toString();

        [$from, $to] = match ($period) {
            'week' => [
                Carbon::now($timezone)->startOfWeek(),
                Carbon::now($timezone)->endOfWeek(),
            ],
            'month' => [
                Carbon::now($timezone)->startOfMonth(),
                Carbon::now($timezone)->endOfMonth(),
            ],
            'custom' => [
                Carbon::parse($request->input('from', Carbon::now($timezone)->toDateString()), $timezone)->startOfDay(),
                Carbon::parse($request->input('to', Carbon::now($timezone)->toDateString()), $timezone)->endOfDay(),
            ],
            default => [
                Carbon::now($timezone)->startOfDay(),
                Carbon::now($timezone)->endOfDay(),
            ],
        };

        return view('admin.reports.index', [
            'period' => $period,
            'report' => $reports->summarize($from, $to),
        ]);
    }
}
