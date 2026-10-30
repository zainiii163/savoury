<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardMetricsService
{
    /**
     * @return array<string, mixed>
     */
    public function summarize(): array
    {
        $timezone = config('savoury.display_timezone');
        $dayStart = Carbon::now($timezone)->startOfDay()->utc();
        $dayEnd = Carbon::now($timezone)->endOfDay()->utc();

        $todayOrders = Order::query()
            ->whereBetween('placed_at', [$dayStart, $dayEnd]);

        $todaySalesMinor = (int) (clone $todayOrders)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->sum('total_minor');

        $todayCount = (clone $todayOrders)->count();

        $byStatus = Order::query()
            ->select('status', DB::raw('count(*) as aggregate'))
            ->whereNotIn('status', ['delivered', 'collected', 'cancelled', 'rejected'])
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $openCount = (int) $byStatus->sum();

        $fulfillmentToday = Order::query()
            ->select('fulfillment_type', DB::raw('count(*) as aggregate'))
            ->whereBetween('placed_at', [$dayStart, $dayEnd])
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->groupBy('fulfillment_type')
            ->pluck('aggregate', 'fulfillment_type');

        $unpaidCashMinor = (int) Order::query()
            ->where('payment_status', 'unpaid')
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->sum('total_minor');

        $recentOrders = Order::query()
            ->orderByDesc('placed_at')
            ->limit(10)
            ->get([
                'id',
                'public_reference',
                'customer_name',
                'fulfillment_type',
                'status',
                'payment_status',
                'total_minor',
                'placed_at',
            ]);

        return [
            'timezone' => $timezone,
            'as_of' => Carbon::now($timezone),
            'ordering_paused' => (bool) RestaurantSetting::getValue('ordering_paused', false),
            'today' => [
                'order_count' => $todayCount,
                'sales_minor' => $todaySalesMinor,
                'sales_pkr' => Money::toMajor($todaySalesMinor),
                'pickup_count' => (int) ($fulfillmentToday['pickup'] ?? 0),
                'delivery_count' => (int) ($fulfillmentToday['delivery'] ?? 0),
            ],
            'open_orders' => [
                'total' => $openCount,
                'by_status' => $this->statusBreakdown($byStatus),
            ],
            'cash' => [
                'unpaid_minor' => $unpaidCashMinor,
                'unpaid_pkr' => Money::toMajor($unpaidCashMinor),
            ],
            'catalogue' => [
                'active_products' => Product::query()->where('is_active', true)->count(),
                'unavailable_products' => Product::query()
                    ->where('is_active', true)
                    ->where('is_available', false)
                    ->count(),
            ],
            'recent_orders' => $recentOrders,
        ];
    }

    /**
     * @param  Collection<string, int|string>  $byStatus
     * @return array<string, int>
     */
    private function statusBreakdown(Collection $byStatus): array
    {
        $keys = ['pending', 'accepted', 'preparing', 'ready', 'out_for_delivery'];
        $out = [];

        foreach ($keys as $key) {
            $out[$key] = (int) ($byStatus[$key] ?? 0);
        }

        return $out;
    }
}
