<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Seed the three provisional poster products.
     *
     * This seeder is intentionally idempotent and must not create any menu
     * items, categories, operating hours, addresses, delivery zones, or fees
     * that were not supplied in the SRS.
     */
    public function run(): void
    {
        $now = now();

        $categoryId = $this->upsertCategory($now);

        foreach ($this->products() as $product) {
            $this->upsertProduct($categoryId, $product, $now);
        }
    }

    private function upsertCategory(\DateTimeInterface $now): int
    {
        $existing = DB::table('categories')->where('slug', 'menu')->first();

        $attributes = [
            'name' => 'Menu',
            'slug' => 'menu',
            'description' => null,
            'image_path' => null,
            'is_active' => true,
            'sort_order' => 0,
            'updated_at' => $now,
            'deleted_at' => null,
        ];

        if ($existing !== null) {
            DB::table('categories')->where('id', $existing->id)->update($attributes);

            return (int) $existing->id;
        }

        return (int) DB::table('categories')->insertGetId($attributes + [
            'created_at' => $now,
        ]);
    }

    /**
     * @param  array{name: string, slug: string, price_minor: int, sort_order: int}  $product
     */
    private function upsertProduct(int $categoryId, array $product, \DateTimeInterface $now): void
    {
        $existing = DB::table('products')->where('slug', $product['slug'])->first();

        $attributes = [
            'category_id' => $categoryId,
            'name' => $product['name'],
            'slug' => $product['slug'],
            'description' => null,
            'price_minor' => $product['price_minor'],
            'image_path' => null,
            'is_available' => true,
            'is_active' => true,
            'sort_order' => $product['sort_order'],
            'updated_at' => $now,
            'deleted_at' => null,
        ];

        if ($existing !== null) {
            DB::table('products')->where('id', $existing->id)->update($attributes);

            return;
        }

        DB::table('products')->insert($attributes + [
            'created_at' => $now,
        ]);
    }

    /**
     * Prices are stored in PKR minor units (paisa): 37500 = PKR 375.00.
     *
     * @return list<array{name: string, slug: string, price_minor: int, sort_order: int}>
     */
    private function products(): array
    {
        return [
            [
                'name' => 'Pulao Kabab',
                'slug' => 'pulao-kabab',
                'price_minor' => 37_500,
                'sort_order' => 1,
            ],
            [
                'name' => 'Single Plate',
                'slug' => 'single-plate',
                'price_minor' => 56_500,
                'sort_order' => 2,
            ],
            [
                'name' => 'Single Without Kabab',
                'slug' => 'single-without-kabab',
                'price_minor' => 46_500,
                'sort_order' => 3,
            ],
        ];
    }
}
