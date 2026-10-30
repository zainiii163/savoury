<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiContractsTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_cart_json_mutations_reprice_on_server(): void
    {
        [$product, $variant] = $this->seedProduct();

        $this->postJson(route('api.cart.items.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])
            ->assertCreated()
            ->assertJsonPath('data.item_count', 1)
            ->assertJsonPath('data.subtotal_pkr', 400)
            ->assertJsonPath('data.total_minor', 40_000);

        $key = $this->getJson(route('api.cart.show'))
            ->assertOk()
            ->json('data.items.0.key');

        $this->patchJson(route('api.cart.items.update', $key), ['quantity' => 2])
            ->assertOk()
            ->assertJsonPath('data.total_pkr', 800);

        $zone = DeliveryZone::query()->create([
            'name' => 'Near Market',
            'slug' => 'near-market',
            'delivery_fee_minor' => 15_000,
            'min_order_minor' => 0,
            'is_active' => true,
        ]);

        $this->getJson(route('api.checkout.preview', [
            'fulfillment_type' => 'delivery',
            'delivery_zone_id' => $zone->id,
        ]))
            ->assertOk()
            ->assertJsonPath('data.fulfillment_type', 'delivery')
            ->assertJsonPath('data.ordering_open', true)
            ->assertJsonPath('data.cart.delivery_fee_pkr', 150)
            ->assertJsonPath('data.cart.total_pkr', 950);

        $this->deleteJson(route('api.cart.items.destroy', $key))
            ->assertOk()
            ->assertJsonPath('data.item_count', 0);
    }

    public function test_admin_inbox_json_requires_orders_permission(): void
    {
        $this->seed();
        $admin = $this->makeAdmin();

        Order::query()->create([
            'public_reference' => 'SS-API-0001',
            'idempotency_key' => 'idem-api-1',
            'tracking_token_hash' => hash('sha256', 'token'),
            'customer_name' => 'Inbox Guest',
            'phone' => '03166100080',
            'fulfillment_type' => 'pickup',
            'payment_method' => 'cash_on_pickup',
            'subtotal_minor' => 37_500,
            'delivery_fee_minor' => 0,
            'total_minor' => 37_500,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'placed_at' => now('UTC'),
        ]);

        $this->getJson(route('admin.api.orders.inbox'))->assertUnauthorized();

        $this->actingAs($admin)
            ->getJson(route('admin.api.orders.inbox'))
            ->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.public_reference', 'SS-API-0001')
            ->assertJsonPath('data.0.status_label', 'Pending')
            ->assertJsonPath('data.0.total_pkr', 375);
    }

    public function test_customer_journey_blade_checkout_then_status_json(): void
    {
        [$product, $variant] = $this->seedProduct();

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect(route('cart.show'));

        $this->post(route('checkout.store'), [
            'customer_name' => 'Journey Guest',
            'phone' => '03166100080',
            'fulfillment_type' => 'pickup',
            'payment_method' => 'cash_on_pickup',
            'idempotency_key' => 'idem-journey-1',
        ])->assertRedirect();

        $order = Order::query()->firstOrFail();

        $this->get(route('orders.confirmation', $order->public_reference))
            ->assertOk()
            ->assertSee('Track this order', false);

        $this->getJson(route('orders.track.status', $order->public_reference))
            ->assertOk()
            ->assertJsonPath('public_reference', $order->public_reference)
            ->assertJsonPath('status', 'pending')
            ->assertJsonStructure(['timeline', 'status_label', 'is_terminal']);
    }

    /**
     * @return array{0: Product, 1: \App\Models\ProductVariant}
     */
    private function seedProduct(): array
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

        return [$product, $variant];
    }

    private function makeAdmin(): User
    {
        $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();

        $user = User::factory()->create([
            'email' => 'api-admin@savoury.test',
            'password' => Hash::make('password-password'),
            'is_active' => true,
        ]);

        $user->roles()->attach($adminRole->id);

        return $user;
    }
}
