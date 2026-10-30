<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_responses_include_baseline_security_headers(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_inactive_admin_cannot_sign_in(): void
    {
        $this->seed();
        $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();

        $user = User::factory()->create([
            'email' => 'inactive@savoury.test',
            'password' => Hash::make('password-password'),
            'is_active' => false,
        ]);
        $user->roles()->attach($adminRole->id);

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => 'inactive@savoury.test',
                'password' => 'password-password',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_guests_cannot_mutate_orders_or_collect_cash(): void
    {
        $order = Order::query()->create([
            'public_reference' => 'SS-SEC-0001',
            'idempotency_key' => 'idem-sec-1',
            'tracking_token_hash' => hash('sha256', 'sec-token'),
            'customer_name' => 'Sec Guest',
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

        $this->post(route('admin.orders.transition', $order), ['status' => 'accepted'])
            ->assertRedirect(route('admin.login'));

        $this->post(route('admin.orders.collect', $order))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.orders.show', $order))
            ->assertRedirect(route('admin.login'));
    }

    public function test_checkout_ignores_client_supplied_money_fields(): void
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

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price_minor' => 1,
            'total_minor' => 1,
        ])->assertRedirect(route('cart.show'));

        $this->post(route('checkout.store'), [
            'customer_name' => 'Tamper Guest',
            'phone' => '03166100080',
            'fulfillment_type' => 'pickup',
            'payment_method' => 'cash_on_pickup',
            'idempotency_key' => 'idem-tamper-1',
            'total_minor' => 1,
            'subtotal_minor' => 1,
        ])->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame(37_500, $order->total_minor);
        $this->assertSame(37_500, $order->subtotal_minor);
    }

    public function test_health_endpoint_is_public(): void
    {
        $this->get('/up')->assertOk();
    }
}
