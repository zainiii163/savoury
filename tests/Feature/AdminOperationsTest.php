<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Payment;
use App\Models\RestaurantSetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_transition_order_collect_cash_and_print_ticket(): void
    {
        $this->seed();
        $admin = $this->makeAdmin();
        $order = $this->makeOrder();

        $this->actingAs($admin)
            ->post(route('admin.orders.transition', $order), [
                'status' => 'accepted',
                'reason' => 'Confirmed by phone',
            ])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame('accepted', $order->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.orders.collect', $order), [
                'notes' => 'Cash received at counter',
            ])
            ->assertRedirect(route('admin.orders.show', $order));

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('paid', $order->payments()->first()->status);
        $this->assertSame(1, AuditLog::query()->where('event', 'cash_collected')->count());

        $this->actingAs($admin)
            ->get(route('admin.orders.ticket', $order))
            ->assertOk()
            ->assertSee($order->public_reference, false)
            ->assertSee('Pulao Kabab', false);
    }

    public function test_admin_can_configure_settings_hours_and_zones(): void
    {
        $this->seed();
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'restaurant_name' => 'Savoury Spot',
                'phone' => '0316-6100080',
                'address' => 'Shorkot City',
                'ordering_paused' => '1',
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertTrue((bool) RestaurantSetting::getValue('ordering_paused', false));
        $this->assertSame('Shorkot City', RestaurantSetting::getValue('address'));

        $hours = [];
        for ($day = 0; $day <= 6; $day++) {
            $hours[] = [
                'day_of_week' => $day,
                'is_closed' => $day === 0 ? '1' : null,
                'opens_at' => $day === 0 ? null : '11:00',
                'closes_at' => $day === 0 ? null : '22:00',
            ];
        }

        $this->actingAs($admin)
            ->put(route('admin.settings.hours'), ['hours' => $hours])
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertDatabaseHas('business_hours', [
            'day_of_week' => 1,
            'is_closed' => 0,
        ]);
        $this->assertTrue(
            in_array(
                \App\Models\BusinessHour::query()->where('day_of_week', 1)->value('opens_at'),
                ['11:00', '11:00:00'],
                true
            )
        );

        $this->actingAs($admin)
            ->post(route('admin.delivery-zones.store'), [
                'name' => 'Near Market',
                'delivery_fee_pkr' => 50,
                'min_order_pkr' => 300,
                'coverage_notes' => 'Owner-confirmed coverage',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.delivery-zones.index'));

        $zone = DeliveryZone::query()->where('slug', 'near-market')->first();
        $this->assertNotNull($zone);
        $this->assertSame(5_000, $zone->delivery_fee_minor);
        $this->assertSame(30_000, $zone->min_order_minor);
    }

    public function test_reports_summarize_sales_and_product_performance(): void
    {
        $this->seed();
        $admin = $this->makeAdmin();
        $order = $this->makeOrder();

        $this->actingAs($admin)
            ->get(route('admin.reports.index', ['period' => 'today']))
            ->assertOk()
            ->assertSee('Reports', false)
            ->assertSee('PKR 375', false)
            ->assertSee('Pulao Kabab', false)
            ->assertSee('Cash outstanding', false);

        $this->assertSame(37_500, $order->total_minor);
    }

    public function test_menu_only_admin_cannot_manage_orders_without_permission_path(): void
    {
        $this->seed();

        // Staff has manage-orders but not manage-settings.
        $staff = $this->makeStaff();

        $this->actingAs($staff)
            ->get(route('admin.orders.index'))
            ->assertOk();

        $this->actingAs($staff)
            ->get(route('admin.settings.edit'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('admin.reports.index'))
            ->assertOk();
    }

    private function makeAdmin(): User
    {
        $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();

        $user = User::factory()->create([
            'email' => 'ops-admin@savoury.test',
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
            'email' => 'ops-staff@savoury.test',
            'password' => Hash::make('password-password'),
            'is_active' => true,
        ]);

        $user->roles()->attach($staffRole->id);

        return $user;
    }

    private function makeOrder(): Order
    {
        $order = Order::query()->create([
            'public_reference' => 'SS-OPS-0001',
            'idempotency_key' => 'idem-ops-1',
            'tracking_token_hash' => hash('sha256', 'ops-token'),
            'customer_name' => 'Ops Guest',
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

        $order->items()->create([
            'product_name' => 'Pulao Kabab',
            'unit_price_minor' => 37_500,
            'addons_total_minor' => 0,
            'quantity' => 1,
            'line_total_minor' => 37_500,
            'addons_snapshot' => [],
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'method' => 'cash_on_pickup',
            'amount_minor' => 37_500,
            'status' => 'unpaid',
        ]);

        return $order;
    }
}
