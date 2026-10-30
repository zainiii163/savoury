<?php

namespace App\Services\Menu;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProductWriter
{
    public function __construct(
        private readonly ProductImageUploader $images,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<array{name: string, price_minor: int, is_default: bool, is_available: bool, sort_order: int}>  $variants
     * @param  list<int>  $addonIds
     */
    public function create(array $attributes, array $variants, array $addonIds, ?UploadedFile $image = null): Product
    {
        return DB::transaction(function () use ($attributes, $variants, $addonIds, $image) {
            if ($image !== null) {
                $attributes['image_path'] = $this->images->store($image);
            }

            $product = Product::query()->create($attributes);
            $this->syncVariants($product, $variants);
            $product->addons()->sync($addonIds);

            return $product->fresh(['category', 'variants', 'addons']);
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<array{name: string, price_minor: int, is_default: bool, is_available: bool, sort_order: int}>  $variants
     * @param  list<int>  $addonIds
     */
    public function update(
        Product $product,
        array $attributes,
        array $variants,
        array $addonIds,
        ?UploadedFile $image = null,
        bool $removeImage = false,
    ): Product {
        return DB::transaction(function () use ($product, $attributes, $variants, $addonIds, $image, $removeImage) {
            if ($removeImage && $image === null) {
                $this->images->delete($product->image_path);
                $attributes['image_path'] = null;
            }

            if ($image !== null) {
                $this->images->delete($product->image_path);
                $attributes['image_path'] = $this->images->store($image);
            }

            $product->update($attributes);
            $this->syncVariants($product, $variants);
            $product->addons()->sync($addonIds);

            return $product->fresh(['category', 'variants', 'addons']);
        });
    }

    /**
     * @param  list<array{name: string, price_minor: int, is_default: bool, is_available: bool, sort_order: int}>  $variants
     */
    private function syncVariants(Product $product, array $variants): void
    {
        $product->variants()->withTrashed()->forceDelete();

        foreach ($variants as $variant) {
            $product->variants()->create($variant);
        }
    }
}
