<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $products = Product::query()
            ->visible()
            ->with($this->catalogueRelations())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return ProductResource::collection($products);
    }

    public function show(string $slug): ProductResource
    {
        $product = Product::query()
            ->visible()
            ->where('slug', $slug)
            ->with($this->catalogueRelations())
            ->firstOrFail();

        return new ProductResource($product);
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogueRelations(): array
    {
        return [
            'category',
            'variants' => fn ($query) => $query
                ->where('is_available', true)
                ->orderBy('sort_order'),
            'addons' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order'),
        ];
    }
}
