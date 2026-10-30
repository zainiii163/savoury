<?php

namespace App\Services\Orders;

use App\Models\Order;

class OrderStatusPresenter
{
    public function label(string $status, ?string $fulfillmentType = null): string
    {
        return match ($status) {
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'preparing' => 'Preparing',
            'ready' => $fulfillmentType === 'pickup' ? 'Ready for Pickup' : 'Ready',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'collected' => 'Collected',
            'cancelled' => 'Cancelled',
            'rejected' => 'Rejected',
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }

    public function forOrder(Order $order): string
    {
        return $this->label($order->status, $order->fulfillment_type);
    }

    /**
     * Customer-visible progress steps for the current fulfilment type.
     *
     * @return list<array{key: string, label: string, state: 'done'|'current'|'upcoming'|'skipped'}>
     */
    public function timeline(Order $order): array
    {
        $steps = $order->fulfillment_type === 'delivery'
            ? ['pending', 'accepted', 'preparing', 'ready', 'out_for_delivery', 'delivered']
            : ['pending', 'accepted', 'preparing', 'ready', 'collected'];

        if (in_array($order->status, ['cancelled', 'rejected'], true)) {
            return array_map(
                fn (string $key) => [
                    'key' => $key,
                    'label' => $this->label($key, $order->fulfillment_type),
                    'state' => 'skipped',
                ],
                $steps
            );
        }

        $currentIndex = array_search($order->status, $steps, true);
        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        $timeline = [];

        foreach ($steps as $index => $key) {
            $state = match (true) {
                $index < $currentIndex => 'done',
                $index === $currentIndex => 'current',
                default => 'upcoming',
            };

            $timeline[] = [
                'key' => $key,
                'label' => $this->label($key, $order->fulfillment_type),
                'state' => $state,
            ];
        }

        return $timeline;
    }
}
