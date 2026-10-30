<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderStatusTransitionService
{
    /**
     * @var array<string, list<string>>
     */
    private const BASE = [
        'pending' => ['accepted', 'cancelled', 'rejected'],
        'accepted' => ['preparing', 'cancelled', 'rejected'],
        'preparing' => ['ready', 'cancelled'],
        'cancelled' => [],
        'rejected' => [],
    ];

    /**
     * @var array<string, list<string>>
     */
    private const PICKUP = [
        'ready' => ['collected', 'cancelled'],
        'collected' => [],
    ];

    /**
     * @var array<string, list<string>>
     */
    private const DELIVERY = [
        'ready' => ['out_for_delivery', 'cancelled'],
        'out_for_delivery' => ['delivered', 'cancelled'],
        'delivered' => [],
    ];

    /**
     * @return list<string>
     */
    public function allowedTargets(Order $order): array
    {
        $map = $this->mapFor($order->fulfillment_type);

        return $map[$order->status] ?? [];
    }

    public function canTransition(Order $order, string $toStatus): bool
    {
        return in_array($toStatus, $this->allowedTargets($order), true);
    }

    public function transition(Order $order, string $toStatus, ?User $actor = null, ?string $reason = null): Order
    {
        if (! in_array($toStatus, config('savoury.order_statuses'), true)) {
            throw ValidationException::withMessages([
                'status' => 'Unknown order status.',
            ]);
        }

        if (! $this->canTransition($order, $toStatus)) {
            throw ValidationException::withMessages([
                'status' => sprintf(
                    'Cannot move a %s order from %s to %s.',
                    $order->fulfillment_type,
                    $order->status,
                    $toStatus
                ),
            ]);
        }

        return DB::transaction(function () use ($order, $toStatus, $actor, $reason) {
            /** @var Order $locked */
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $this->canTransition($locked, $toStatus)) {
                throw ValidationException::withMessages([
                    'status' => 'The order status changed. Refresh and try again.',
                ]);
            }

            $from = $locked->status;

            $locked->status = $toStatus;

            if (in_array($toStatus, ['delivered', 'collected'], true)) {
                $locked->completed_at = now('UTC');
            }

            $locked->save();

            $locked->statusHistories()->create([
                'from_status' => $from,
                'to_status' => $toStatus,
                'changed_by' => $actor?->id,
                'reason' => $reason,
                'created_at' => now('UTC'),
            ]);

            return $locked->fresh(['items', 'statusHistories', 'payments']);
        });
    }

    /**
     * @return array<string, list<string>>
     */
    private function mapFor(string $fulfillmentType): array
    {
        return match ($fulfillmentType) {
            'pickup' => array_merge(self::BASE, self::PICKUP),
            'delivery' => array_merge(self::BASE, self::DELIVERY),
            default => self::BASE,
        };
    }
}
