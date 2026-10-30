<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * @return array<string, mixed>
     */
    public function summarize(Carbon $from, Carbon $to): array
    {
        $fromUtc = $from->copy()->utc();
        $toUtc = $to->copy()->utc();

        $base = Order::query()->whereBetween('placed_at', [$fromUtc, $toUtc]);

        $salesMinor = (int) (clone $base)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->sum('total_minor');

        $orderCount = (clone $base)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->count();

        $cancelledCount = (clone $base)
            ->whereIn('status', ['cancelled', 'rejected'])
            ->count();

        $fulfillment = Order::query()
            ->select('fulfillment_type', DB::raw('count(*) as aggregate'), DB::raw('sum(total_minor) as sales_minor'))
            ->whereBetween('placed_at', [$fromUtc, $toUtc])
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->groupBy('fulfillment_type')
            ->get()
            ->keyBy('fulfillment_type');

        $cashPaidMinor = (int) Order::query()
            ->whereBetween('placed_at', [$fromUtc, $toUtc])
            ->where('payment_status', 'paid')
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->sum('total_minor');

        $cashUnpaidMinor = (int) Order::query()
            ->whereBetween('placed_at', [$fromUtc, $toUtc])
            ->where('payment_status', 'unpaid')
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->sum('total_minor');

        $products = OrderItem::query()
            ->select(
                'product_name',
                DB::raw('sum(quantity) as qty'),
                DB::raw('sum(line_total_minor) as sales_minor')
            )
            ->whereHas('order', function ($query) use ($fromUtc, $toUtc) {
                $query->whereBetween('placed_at', [$fromUtc, $toUtc])
                    ->whereNotIn('status', ['cancelled', 'rejected']);
            })
            ->groupBy('product_name')
            ->orderByDesc('qty')
            ->limit(20)
            ->get();

        return [
            'from' => $from,
            'to' => $to,
            'order_count' => $orderCount,
            'cancelled_count' => $cancelledCount,
            'sales_minor' => $salesMinor,
            'sales_pkr' => Money::toMajor($salesMinor),
            'fulfillment' => [
                'pickup' => [
                    'count' => (int) ($fulfillment->get('pickup')?->aggregate ?? 0),
                    'sales_minor' => (int) ($fulfillment->get('pickup')?->sales_minor ?? 0),
                ],
                'delivery' => [
                    'count' => (int) ($fulfillment->get('delivery')?->aggregate ?? 0),
                    'sales_minor' => (int) ($fulfillment->get('delivery')?->sales_minor ?? 0),
                ],
            ],
            'cash' => [
                'paid_minor' => $cashPaidMinor,
                'unpaid_minor' => $cashUnpaidMinor,
            ],
            'products' => $products,
        ];
    }
}
