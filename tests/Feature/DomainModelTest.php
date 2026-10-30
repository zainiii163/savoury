<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DomainModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogue_relations_and_featured_scope(): void
    {
        $category = Category::create([
            'name' => 'Menu',
            'slug' => 'menu',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Pulao Kabab',
            'slug' => 'pulao-kabab',
            'price_minor' => 37_500,
            'is_available' => true,
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 1,
        ]);

        $product->variants()->create([
            'name' => 'Full',
            'price_minor' => 40_000,
            'is_default' => true,
            'is_available' => true,
            'sort_order' => 1,
        ]);

        $addon = Addon::create([
            'name' => 'Raita',
            'slug' => 'raita',
            'price_minor' => 5_000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product->addons()->attach($addon->id);

        $this->assertTrue($product->category->is($category));
        $this->assertCount(1, $product->variants);
        $this->assertTrue($product->addons->first()->is($addon));
        $this->assertSame(1, Product::query()->featured()->count());
    }

    public function test_order_relations_persist_money_snapshots_and_tracking_hash(): void
    {
        $zone = DeliveryZone::create([
            'name' => 'Test Zone',
            'slug' => 'test-zone',
            'delivery_fee_minor' => 0,
            'min_order_minor' => 0,
            'coverage_notes' => null,
            'is_active' => true,
        ]);

        $order = Order::create([
            'public_reference' => 'SS-TEST-0001',
            'idempotency_key' => 'idem-1',
            'tracking_token_hash' => hash('sha256', 'unguessable-token'),
            'customer_name' => 'Test Customer',
            'phone' => '03166100080',
            'fulfillment_type' => 'delivery',
            'payment_method' => 'cash_on_delivery',
            'delivery_zone_id' => $zone->id,
            'delivery_zone_name' => $zone->name,
            'address' => 'Shorkot City',
            'subtotal_minor' => 37_500,
            'delivery_fee_minor' => 0,
            'total_minor' => 37_500,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'placed_at' => now(),
        ]);

        $order->items()->create([
            'product_name' => 'Pulao Kabab',
            'unit_price_minor' => 37_500,
            'addons_total_minor' => 0,
            'quantity' => 1,
            'line_total_minor' => 37_500,
            'addons_snapshot' => [],
        ]);

        $order->payments()->create([
            'method' => 'cash_on_delivery',
            'amount_minor' => 37_500,
            'status' => 'unpaid',
        ]);

        $order->statusHistories()->create([
            'from_status' => null,
            'to_status' => 'pending',
            'created_at' => now(),
        ]);

        $this->assertTrue($order->deliveryZone->is($zone));
        $this->assertCount(1, $order->items);
        $this->assertCount(1, $order->payments);
        $this->assertCount(1, $order->statusHistories);
        $this->assertSame(64, strlen($order->tracking_token_hash));
    }

    public function test_user_role_assignment_without_seeded_passwords(): void
    {
        $this->seed();

        $this->assertGreaterThan(0, Role::query()->count());
        $this->assertGreaterThan(0, Permission::query()->count());
        $this->assertSame(0, User::query()->count());

        $user = User::factory()->create([
            'password' => Hash::make('secret-password'),
            'is_active' => true,
        ]);

        $admin = Role::query()->where('slug', 'admin')->firstOrFail();
        $user->roles()->attach($admin->id);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($admin->permissions->contains('slug', 'manage-menu'));
    }
}
