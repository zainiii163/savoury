<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashCollectionService
{
    public function collect(Order $order, User $collector, ?string $notes = null): Order
    {
        if (in_array($order->status, ['cancelled', 'rejected'], true)) {
            throw ValidationException::withMessages([
                'payment' => 'Cannot collect cash for a cancelled or rejected order.',
            ]);
        }

        if ($order->payment_status === 'paid') {
            throw ValidationException::withMessages([
                'payment' => 'Cash for this order is already marked paid.',
            ]);
        }

        return DB::transaction(function () use ($order, $collector, $notes) {
            /** @var Order $locked */
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->payment_status === 'paid') {
                throw ValidationException::withMessages([
                    'payment' => 'Cash for this order is already marked paid.',
                ]);
            }

            $payment = $locked->payments()
                ->where('status', 'unpaid')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($payment === null) {
                throw ValidationException::withMessages([
                    'payment' => 'No unpaid payment record exists for this order.',
                ]);
            }

            $payment->update([
                'status' => 'paid',
                'collected_by' => $collector->id,
                'paid_at' => now('UTC'),
                'notes' => $notes,
            ]);

            $locked->payment_status = 'paid';
            $locked->save();

            AuditLog::query()->create([
                'user_id' => $collector->id,
                'auditable_type' => Order::class,
                'auditable_id' => $locked->id,
                'event' => 'cash_collected',
                'old_values' => ['payment_status' => 'unpaid'],
                'new_values' => [
                    'payment_status' => 'paid',
                    'amount_minor' => $payment->amount_minor,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now('UTC'),
            ]);

            return $locked->fresh(['items', 'payments', 'statusHistories']);
        });
    }
}
