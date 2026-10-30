<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_update_and_remove_cart_items_with_server_prices(): void
    {
        [$product, $variant, $addon] = $this->seedMenuItem();

        $this->from(route('menu.show', $product->slug))
            ->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'addon_ids' => [$addon->id],
                'quantity' => 1,
            ])
            ->assertRedirect(route('cart.show'));

        $this->get(route('cart.show'))
            ->assertOk()
            ->assertSee('Pulao Kabab', false)
            ->assertSee('Full', false)
            ->assertSee('Raita', false)
            ->assertSee('PKR 450', false);

        $key = app(\App\Services\Cart\SessionCartService::class)
            ->lineKey($product->id, $variant->id, [$addon->id]);

        $this->patch(route('cart.items.update', $key), ['quantity' => 2])
            ->assertRedirect(route('cart.show'));

        $this->get(route('cart.show'))
            ->assertOk()
            ->assertSee('PKR 900', false);

        $this->delete(route('cart.items.destroy', $key))
            ->assertRedirect(route('cart.show'));

        $this->get(route('cart.show'))
            ->assertOk()
            ->assertSee('Your cart is empty.', false);
    }

    public function test_empty_checkout_redirects_to_cart(): void
    {
        $this->get(route('checkout.show'))
            ->assertRedirect(route('cart.show'));
    }

    public function test_pickup_checkout_creates_order_payment_and_confirmation(): void
    {
        [$product, $variant] = $this->seedMenuItem();

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect(route('cart.show'));

        $idempotencyKey = 'idem-pickup-1';

        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'Ayesha Khan',
            'phone' => '03166100080',
            'fulfillment_type' => 'pickup',
            'payment_method' => 'cash_on_pickup',
            'notes' => 'No onions',
            'idempotency_key' => $idempotencyKey,
        ]);

        $order = Order::query()->where('idempotency_key', $idempotencyKey)->first();
        $this->assertNotNull($order);
        $this->assertSame(40_000, $order->subtotal_minor);
        $this->assertSame(0, $order->delivery_fee_minor);
        $this->assertSame(40_000, $order->total_minor);
        $this->assertSame('pending', $order->status);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertCount(1, $order->items);
        $this->assertCount(1, $order->payments);
        $this->assertSame('cash_on_pickup', $order->payments->first()->method);
        $this->assertSame(64, strlen((string) $order->tracking_token_hash));

        $response->assertRedirect(route('orders.confirmation', $order->public_reference));

        $this->get(route('orders.confirmation', $order->public_reference))
            ->assertOk()
            ->assertSee($order->public_reference, false)
            ->assertSee('Save your tracking token now', false)
            ->assertSee('Ayesha Khan', false);

        $this->get(route('cart.show'))
            ->assertOk()
            ->assertSee('Your cart is empty.', false);
    }

    public function test_duplicate_idempotency_key_does_not_create_second_order(): void
    {
        [$product, $variant] = $this->seedMenuItem();

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $payload = [
            'customer_name' => 'Ayesha Khan',
            'phone' => '03166100080',
            'fulfillment_type' => 'pickup',
            'payment_method' => 'cash_on_pickup',
            'idempotency_key' => 'idem-dup-1',
        ];

        $this->post(route('checkout.store'), $payload)->assertRedirect();
        $this->assertSame(1, Order::query()->count());

        // Cart was cleared; refill and replay the same key.
        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->post(route('checkout.store'), $payload)->assertRedirect();
        $this->assertSame(1, Order::query()->count());
    }

    public function test_confirmation_requires_session_from_checkout(): void
    {
        $order = Order::query()->create([
            'public_reference' => 'SS-TEST-CONF',
            'idempotency_key' => 'idem-conf',
            'tracking_token_hash' => hash('sha256', 'token'),
            'customer_name' => 'Guest',
            'phone' => '03166100080',
            'fulfillment_type' => 'pickup',
            'payment_method' => 'cash_on_pickup',
            'subtotal_minor' => 10_000,
            'delivery_fee_minor' => 0,
            'total_minor' => 10_000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'placed_at' => now('UTC'),
        ]);

        $this->get(route('orders.confirmation', $order->public_reference))
            ->assertRedirect(route('orders.track'));
    }

    public function test_delivery_checkout_requires_active_zone_and_uses_zone_fee(): void
    {
        [$product, $variant] = $this->seedMenuItem();

        $zone = DeliveryZone::query()->create([
            'name' => 'Near Market',
            'slug' => 'near-market',
            'delivery_fee_minor' => 15_000,
            'min_order_minor' => 0,
            'is_active' => true,
        ]);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->post(route('checkout.store'), [
            'customer_name' => 'Delivery Guest',
            'phone' => '03166100080',
            'fulfillment_type' => 'delivery',
            'payment_method' => 'cash_on_delivery',
            'delivery_zone_id' => $zone->id,
            'address' => 'Shorkot City, near market',
            'idempotency_key' => 'idem-delivery-1',
        ])->assertRedirect();

        $order = Order::query()->first();
        $this->assertSame(40_000, $order->subtotal_minor);
        $this->assertSame(15_000, $order->delivery_fee_minor);
        $this->assertSame(55_000, $order->total_minor);
        $this->assertSame('Near Market', $order->delivery_zone_name);
    }

    public function test_delivery_without_zones_is_rejected(): void
    {
        [$product, $variant] = $this->seedMenuItem();

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->from(route('checkout.show'))
            ->post(route('checkout.store'), [
                'customer_name' => 'Guest',
                'phone' => '03166100080',
                'fulfillment_type' => 'delivery',
                'payment_method' => 'cash_on_delivery',
                'address' => 'Somewhere',
                'idempotency_key' => 'idem-no-zone',
            ])
            ->assertRedirect(route('checkout.show'))
            ->assertSessionHasErrors('delivery_zone_id');

        $this->assertSame(0, Order::query()->count());
    }

    public function test_ordering_pause_blocks_checkout(): void
    {
        [$product, $variant] = $this->seedMenuItem();

        RestaurantSetting::query()->create([
            'key' => 'ordering_paused',
            'value' => '1',
            'type' => 'bool',
            'group' => 'ordering',
        ]);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->from(route('checkout.show'))
            ->post(route('checkout.store'), [
                'customer_name' => 'Guest',
                'phone' => '03166100080',
                'fulfillment_type' => 'pickup',
                'payment_method' => 'cash_on_pickup',
                'idempotency_key' => 'idem-paused',
            ])
            ->assertRedirect(route('checkout.show'))
            ->assertSessionHasErrors('ordering');

        $this->assertSame(0, Order::query()->count());
    }

    /**
     * @return array{0: Product, 1: \App\Models\ProductVariant, 2?: Addon}
     */
    private function seedMenuItem(): array
    {
        $category = Category::query()->create([
            'name' => 'Menu',
            'slug' => 'menu',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Pulao Kabab',
            'slug' => 'pulao-kabab',
            'price_minor' => 37_500,
            'is_available' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $variant = $product->variants()->create([
            'name' => 'Full',
            'price_minor' => 40_000,
            'is_default' => true,
            'is_available' => true,
            'sort_order' => 1,
        ]);

        $addon = Addon::query()->create([
            'name' => 'Raita',
            'slug' => 'raita',
            'price_minor' => 5_000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product->addons()->attach($addon->id);

        return [$product, $variant, $addon];
    }
}
