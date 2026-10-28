<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalogue_returns_the_product_view_model(): void
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
            'description' => null,
            'price_minor' => 37_500,
            'is_available' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product->variants()->create([
            'name' => 'Full',
            'price_minor' => 40_000,
            'is_default' => true,
            'is_available' => true,
            'sort_order' => 1,
        ]);

        $product->variants()->create([
            'name' => 'Unavailable option',
            'price_minor' => 99_000,
            'is_default' => false,
            'is_available' => false,
            'sort_order' => 2,
        ]);

        $addon = Addon::create([
            'name' => 'Raita',
            'slug' => 'raita',
            'price_minor' => 5_000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $inactiveAddon = Addon::create([
            'name' => 'Inactive addon',
            'slug' => 'inactive-addon',
            'price_minor' => 7_000,
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $product->addons()->attach([$addon->id, $inactiveAddon->id]);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.variants')
            ->assertJsonCount(1, 'data.0.addons')
            ->assertJsonPath('data.0.id', $product->id)
            ->assertJsonPath('data.0.slug', 'pulao-kabab')
            ->assertJsonPath('data.0.price_pkr', 375)
            ->assertJsonPath('data.0.available', true)
            ->assertJsonPath('data.0.category.slug', 'menu')
            ->assertJsonPath('data.0.variants.0.name', 'Full')
            ->assertJsonPath('data.0.variants.0.price_pkr', 400)
            ->assertJsonPath('data.0.addons.0.name', 'Raita')
            ->assertJsonPath('data.0.addons.0.price_pkr', 50)
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'slug',
                    'name',
                    'description',
                    'price_pkr',
                    'image_url',
                    'available',
                    'category' => ['id', 'slug', 'name'],
                    'variants' => [['id', 'name', 'price_pkr', 'available', 'is_default']],
                    'addons' => [['id', 'slug', 'name', 'price_pkr']],
                ]],
            ]);

        $this->getJson('/api/products/pulao-kabab')
            ->assertOk()
            ->assertJsonPath('data.slug', 'pulao-kabab')
            ->assertJsonPath('data.price_pkr', 375);
    }

    public function test_inactive_products_categories_and_unknown_slugs_are_not_public(): void
    {
        $category = Category::create([
            'name' => 'Menu',
            'slug' => 'menu',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Hidden Product',
            'slug' => 'hidden-product',
            'price_minor' => 10_000,
            'is_available' => true,
            'is_active' => false,
            'sort_order' => 1,
        ]);

        $inactiveCategory = Category::create([
            'name' => 'Hidden Category',
            'slug' => 'hidden-category',
            'is_active' => false,
            'sort_order' => 1,
        ]);

        Product::create([
            'category_id' => $inactiveCategory->id,
            'name' => 'Hidden By Category',
            'slug' => 'hidden-by-category',
            'price_minor' => 20_000,
            'is_available' => true,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson('/api/products/hidden-product')->assertNotFound();
        $this->getJson('/api/products/hidden-by-category')->assertNotFound();
        $this->getJson('/api/products/does-not-exist')->assertNotFound();
    }
}
