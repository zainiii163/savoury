<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_dashboard(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_users_without_admin_permissions_are_forbidden(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'password' => Hash::make('password-password'),
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_staff_can_view_dashboard_but_not_menu_crud(): void
    {
        $this->seed();
        $staff = $this->makeStaff();

        $this->actingAs($staff)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard', false)
            ->assertSee('Orders today', false)
            ->assertDontSee('>Products</a>', false);

        $this->actingAs($staff)
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_dashboard_metrics_reflect_real_orders_and_catalogue(): void
    {
        $this->seed();
        $admin = $this->makeAdmin();

        RestaurantSetting::query()->create([
            'key' => 'ordering_paused',
            'value' => '1',
            'type' => 'bool',
            'group' => 'ordering',
        ]);

        Order::query()->create([
            'public_reference' => 'SS-DASH-0001',
            'idempotency_key' => 'idem-dash-1',
            'tracking_token_hash' => hash('sha256', 'token-dash-1'),
            'customer_name' => 'Dashboard Guest',
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

        Order::query()->create([
            'public_reference' => 'SS-DASH-0002',
            'idempotency_key' => 'idem-dash-2',
            'tracking_token_hash' => hash('sha256', 'token-dash-2'),
            'customer_name' => 'Cancelled Guest',
            'phone' => '03166100081',
            'fulfillment_type' => 'delivery',
            'payment_method' => 'cash_on_delivery',
            'subtotal_minor' => 10_000,
            'delivery_fee_minor' => 0,
            'total_minor' => 10_000,
            'status' => 'cancelled',
            'payment_status' => 'unpaid',
            'placed_at' => now('UTC'),
        ]);

        Product::query()->where('slug', 'pulao-kabab')->update(['is_available' => false]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('SS-DASH-0001', false)
            ->assertSee('Dashboard Guest', false)
            ->assertSee('PKR 375', false)
            ->assertSee('Ordering is paused', false)
            ->assertSee('Marked unavailable', false);
    }

    public function test_login_lands_on_dashboard(): void
    {
        $this->seed();
        $admin = $this->makeAdmin();

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password-password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    private function makeAdmin(): User
    {
        $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();

        $user = User::factory()->create([
            'email' => 'admin@savoury.test',
            'password' => Hash::make('password-password'),
            'is_active' => true,
        ]);

        $user->roles()->attach($adminRole->id);

        return $user;
    }

    private function makeStaff(): User
    {
        $staffRole = Role::query()->where('slug', 'staff')->firstOrFail();

        $user = User::factory()->create([
            'email' => 'staff@savoury.test',
            'password' => Hash::make('password-password'),
            'is_active' => true,
        ]);

        $user->roles()->attach($staffRole->id);

        return $user;
    }
}
