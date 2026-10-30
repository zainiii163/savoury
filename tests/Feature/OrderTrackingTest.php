<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\Orders\OrderStatusTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_track_order_with_reference_and_token(): void
    {
        $token = 'tracking-token-abcdefghijklmnopqrstuvwxyz';
        $order = $this->makeOrder('pickup', $token);

        $this->from(route('orders.track'))
            ->post(route('orders.track.store'), [
                'public_reference' => $order->public_reference,
                'tracking_token' => $token,
            ])
            ->assertRedirect(route('orders.track.show', $order->public_reference));

        $this->get(route('orders.track.show', $order->public_reference))
            ->assertOk()
            ->assertSee($order->public_reference, false)
            ->assertSee('Pending', false)
            ->assertSee('Ready for Pickup', false);

        $this->getJson(route('orders.track.status', $order->public_reference))
            ->assertOk()
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('status_label', 'Pending')
            ->assertJsonPath('is_terminal', false);
    }

    public function test_wrong_token_is_rejected_without_leaking_existence(): void
    {
        $order = $this->makeOrder('pickup', 'correct-token-abcdefghijklmnopqrstuv');

        $this->from(route('orders.track'))
            ->post(route('orders.track.store'), [
                'public_reference' => $order->public_reference,
                'tracking_token' => 'wrong-token-abcdefghijklmnopqrstuvw',
            ])
            ->assertRedirect(route('orders.track'))
            ->assertSessionHasErrors('tracking_token');

        $this->get(route('orders.track.show', $order->public_reference))
            ->assertRedirect(route('orders.track', ['reference' => $order->public_reference]));

        $this->getJson(route('orders.track.status', $order->public_reference))
            ->assertForbidden();
    }

    public function test_pickup_transitions_follow_allowed_path_only(): void
    {
        $order = $this->makeOrder('pickup', 'pickup-token-abcdefghijklmnopqrstuv');
        $service = app(OrderStatusTransitionService::class);

        $service->transition($order, 'accepted');
        $service->transition($order->fresh(), 'preparing');
        $service->transition($order->fresh(), 'ready');
        $service->transition($order->fresh(), 'collected');

        $order->refresh();
        $this->assertSame('collected', $order->status);
        $this->assertNotNull($order->completed_at);
        $this->assertSame(4, $order->statusHistories()->count());

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->transition($order->fresh(), 'delivered');
    }

    public function test_delivery_cannot_mark_collected_and_uses_out_for_delivery(): void
    {
        $order = $this->makeOrder('delivery', 'delivery-token-abcdefghijklmnopqr');
        $service = app(OrderStatusTransitionService::class);

        $service->transition($order, 'accepted');
        $service->transition($order->fresh(), 'preparing');
        $service->transition($order->fresh(), 'ready');

        try {
            $service->transition($order->fresh(), 'collected');
            $this->fail('Delivery order should not transition to collected.');
        } catch (\Illuminate\Validation\ValidationException) {
            // expected
        }

        $service->transition($order->fresh(), 'out_for_delivery');
        $service->transition($order->fresh(), 'delivered');

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertNotNull($order->completed_at);
    }

    public function test_track_form_renders(): void
    {
        $this->get(route('orders.track'))
            ->assertOk()
            ->assertSee('Follow your order', false)
            ->assertSee('Tracking token', false);
    }

    private function makeOrder(string $fulfillment, string $token): Order
    {
        return Order::query()->create([
            'public_reference' => 'SS-TEST-'.strtoupper(substr($fulfillment, 0, 3)),
            'idempotency_key' => 'idem-'.$fulfillment.'-'.uniqid(),
            'tracking_token_hash' => hash('sha256', $token),
            'customer_name' => 'Track Guest',
            'phone' => '03166100080',
            'fulfillment_type' => $fulfillment,
            'payment_method' => $fulfillment === 'delivery' ? 'cash_on_delivery' : 'cash_on_pickup',
            'address' => $fulfillment === 'delivery' ? 'Shorkot City' : null,
            'subtotal_minor' => 37_500,
            'delivery_fee_minor' => 0,
            'total_minor' => 37_500,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'placed_at' => now('UTC'),
        ]);
    }
}
