<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->string('category')->toString();
        $search = $request->string('q')->trim()->toString();

        $categories = Category::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        $activeCategory = $categorySlug !== ''
            ? $categories->firstWhere('slug', $categorySlug)
            : null;

        $products = Product::query()
            ->visible()
            ->available()
            ->with(['category', 'variants' => fn ($query) => $query->where('is_available', true)->orderBy('sort_order')])
            ->when($activeCategory, fn ($query) => $query->where('category_id', $activeCategory->id))
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.$search.'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->orderBy('sort_order')
            ->get();

        return view('menu.index', [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'products' => $products,
            'search' => $search,
            'brand' => config('savoury.brand'),
        ]);
    }

    public function show(string $slug): View
    {
        $product = Product::query()
            ->visible()
            ->where('slug', $slug)
            ->with([
                'category',
                'variants' => fn ($query) => $query->where('is_available', true)->orderBy('sort_order'),
                'addons' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
            ])
            ->firstOrFail();

        return view('menu.show', [
            'product' => $product,
            'brand' => config('savoury.brand'),
        ]);
    }
}
