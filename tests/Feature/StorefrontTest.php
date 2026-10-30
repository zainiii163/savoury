<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_brand_and_seeded_menu_highlights(): void
    {
        $this->seed();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Savoury Spot', false)
            ->assertSee('Special Aroma, Homemade Trust', false)
            ->assertSee('Order now', false)
            ->assertSee('Pulao Kabab', false)
            ->assertSee('Single Plate', false)
            ->assertSee('0316-6100080', false);
    }

    public function test_menu_index_lists_mysql_products_and_supports_category_filter(): void
    {
        $this->seed();

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertSee('Complete menu', false)
            ->assertSee('Pulao Kabab', false)
            ->assertSee('PKR 375', false);

        $this->get(route('menu.index', ['category' => 'menu']))
            ->assertOk()
            ->assertSee('Menu', false)
            ->assertSee('Single Without Kabab', false);
    }

    public function test_product_detail_page_loads_from_database(): void
    {
        $category = Category::create([
            'name' => 'Menu',
            'slug' => 'menu',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Pulao Kabab',
            'slug' => 'pulao-kabab',
            'price_minor' => 37_500,
            'is_available' => true,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        $this->get(route('menu.show', 'pulao-kabab'))
            ->assertOk()
            ->assertSee('Pulao Kabab', false)
            ->assertSee('PKR 375', false)
            ->assertSee('Add to cart', false)
            ->assertSee('Placeholder visual', false);
    }

    public function test_inactive_products_are_hidden_from_storefront_detail(): void
    {
        $category = Category::create([
            'name' => 'Menu',
            'slug' => 'menu',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Hidden',
            'slug' => 'hidden',
            'price_minor' => 10_000,
            'is_available' => true,
            'is_active' => false,
            'sort_order' => 1,
        ]);

        $this->get(route('menu.show', 'hidden'))->assertNotFound();
    }

    public function test_supporting_customer_pages_render(): void
    {
        foreach ([
            'about',
            'contact',
            'privacy',
            'terms',
            'delivery-policy',
            'cart',
            'orders/track',
        ] as $path) {
            $this->get('/'.$path)->assertOk();
        }

        $this->get('/checkout')->assertRedirect(route('cart.show'));
    }
}
