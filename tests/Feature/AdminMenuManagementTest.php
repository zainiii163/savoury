<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMenuManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_admin_menu_management(): void
    {
        $this->get(route('admin.products.index'))->assertRedirect(route('admin.login'));
    }

    public function test_users_without_menu_permission_are_forbidden(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'password' => Hash::make('password-password'),
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_update_and_feature_a_product_with_image(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = $this->makeAdmin();
        $category = Category::query()->where('slug', 'menu')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Chef Special',
                'slug' => 'chef-special',
                'description' => 'Owner-approved description',
                'price_pkr' => 500,
                'is_active' => '1',
                'is_available' => '1',
                'is_featured' => '1',
                'sort_order' => 10,
                'image' => UploadedFile::fake()->image('dish.jpg', 400, 300),
                'variants' => [
                    ['name' => 'Full', 'price_pkr' => 520, 'is_default' => '1', 'is_available' => '1'],
                    ['name' => '', 'price_pkr' => '', 'is_available' => '1'],
                ],
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('slug', 'chef-special')->firstOrFail();

        $this->assertSame(50_000, $product->price_minor);
        $this->assertTrue($product->is_featured);
        $this->assertNotNull($product->image_path);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'name' => 'Full',
            'price_minor' => 52_000,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Chef Special', false)
            ->assertSee('Featured favourites', false);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'category_id' => $category->id,
                'name' => 'Chef Special Updated',
                'slug' => 'chef-special',
                'description' => 'Updated copy',
                'price_pkr' => 510,
                'is_active' => '1',
                'is_available' => '1',
                'is_featured' => '1',
                'sort_order' => 10,
                'variants' => [
                    ['name' => 'Full', 'price_pkr' => 530, 'is_default' => '1', 'is_available' => '1'],
                ],
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Chef Special Updated',
            'price_minor' => 51_000,
        ]);
    }

    public function test_admin_can_manage_categories_and_menu_search_works(): void
    {
        $this->seed();
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Sides',
                'slug' => 'sides',
                'is_active' => '1',
                'sort_order' => 2,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['slug' => 'sides', 'is_active' => true]);

        $this->get(route('menu.index', ['q' => 'Pulao']))
            ->assertOk()
            ->assertSee('Pulao Kabab', false)
            ->assertSee(route('menu.show', 'pulao-kabab'), false)
            ->assertDontSee(route('menu.show', 'single-plate'), false);
    }

    public function test_login_rejects_invalid_credentials_and_accepts_admin(): void
    {
        $this->seed();
        $admin = $this->makeAdmin();

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => $admin->email,
                'password' => 'wrong-password-here',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

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
}
