<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseBootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_mvp_tables_and_phase2_columns_exist(): void
    {
        foreach ([
            'users',
            'roles',
            'permissions',
            'permission_role',
            'role_user',
            'categories',
            'products',
            'product_variants',
            'addons',
            'product_addons',
            'delivery_zones',
            'carts',
            'cart_items',
            'cart_item_addons',
            'orders',
            'order_items',
            'order_status_histories',
            'payments',
            'business_hours',
            'restaurant_settings',
            'audit_logs',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }

        $this->assertTrue(Schema::hasColumn('orders', 'tracking_token_hash'));
        $this->assertTrue(Schema::hasColumn('orders', 'payment_method'));
        $this->assertTrue(Schema::hasColumn('products', 'price_minor'));
        $this->assertTrue(Schema::hasColumn('products', 'is_featured'));
        $this->assertTrue(Schema::hasColumn('delivery_zones', 'min_order_minor'));
        $this->assertTrue(Schema::hasColumn('delivery_zones', 'coverage_notes'));
        $this->assertTrue(Schema::hasColumn('users', 'is_active'));
    }

    public function test_database_seeder_loads_roles_and_provisional_menu_only(): void
    {
        Artisan::call('db:seed');

        $products = DB::table('products')->orderBy('sort_order')->get(['name', 'slug', 'price_minor', 'is_featured']);

        $this->assertCount(3, $products);
        $this->assertSame([
            ['name' => 'Pulao Kabab', 'slug' => 'pulao-kabab', 'price_minor' => 37_500],
            ['name' => 'Single Plate', 'slug' => 'single-plate', 'price_minor' => 56_500],
            ['name' => 'Single Without Kabab', 'slug' => 'single-without-kabab', 'price_minor' => 46_500],
        ], $products->map(fn ($row) => [
            'name' => $row->name,
            'slug' => $row->slug,
            'price_minor' => (int) $row->price_minor,
        ])->all());

        $this->assertTrue($products->every(fn ($row) => (int) $row->is_featured === 0));
        $this->assertSame(1, DB::table('categories')->count());
        $this->assertSame(2, DB::table('roles')->count());
        $this->assertSame(6, DB::table('permissions')->count());
        $this->assertSame(0, DB::table('users')->count());
        $this->assertSame(0, DB::table('delivery_zones')->count());
        $this->assertSame(0, DB::table('business_hours')->count());
        $this->assertSame(0, DB::table('restaurant_settings')->count());
        $this->assertSame(0, DB::table('orders')->count());
    }

    public function test_product_seeder_is_idempotent(): void
    {
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\ProductSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\ProductSeeder']);

        $this->assertSame(3, DB::table('products')->count());
        $this->assertSame(1, DB::table('categories')->count());
    }
}
