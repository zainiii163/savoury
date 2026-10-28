<?php

namespace App\Http\Resources;

use App\Models\Addon;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'price_pkr' => $this->pkr($this->price_minor),
            'image_url' => $this->imageUrl($this->image_path),
            'available' => (bool) $this->is_available,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'slug' => $this->category->slug,
                'name' => $this->category->name,
            ], null),
            'variants' => $this->whenLoaded('variants', fn () => $this->variants
                ->map(fn (ProductVariant $variant) => [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'price_pkr' => $this->pkr($variant->price_minor),
                    'available' => (bool) $variant->is_available,
                    'is_default' => (bool) $variant->is_default,
                ])
                ->values()
                ->all(), []),
            'addons' => $this->whenLoaded('addons', fn () => $this->addons
                ->map(fn (Addon $addon) => [
                    'id' => $addon->id,
                    'slug' => $addon->slug,
                    'name' => $addon->name,
                    'price_pkr' => $this->pkr($addon->price_minor),
                ])
                ->values()
                ->all(), []),
        ];
    }

    /**
     * Convert integer PKR minor units to a display value without float math
     * leaking into server-side totals.
     */
    private function pkr(int $minor): int|float
    {
        return $minor % 100 === 0
            ? intdiv($minor, 100)
            : round($minor / 100, 2);
    }

    private function imageUrl(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        return filter_var($path, FILTER_VALIDATE_URL) !== false
            ? $path
            : asset($path);
    }
}
