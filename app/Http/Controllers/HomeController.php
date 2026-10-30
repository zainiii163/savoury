<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featured = Product::query()
            ->visible()
            ->available()
            ->featured()
            ->with('category')
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        $signatureDishes = $featured->isNotEmpty()
            ? $featured
            : Product::query()
                ->visible()
                ->available()
                ->with('category')
                ->orderBy('sort_order')
                ->take(6)
                ->get();

        $categories = Category::query()
            ->active()
            ->withCount(['products as visible_products_count' => fn ($query) => $query->visible()->available()])
            ->orderBy('sort_order')
            ->get();

        return view('home', [
            'signatureDishes' => $signatureDishes,
            'showingFeaturedOnly' => $featured->isNotEmpty(),
            'categories' => $categories,
            'brand' => config('savoury.brand'),
        ]);
    }
}
