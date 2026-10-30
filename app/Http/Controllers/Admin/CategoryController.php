<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        $categories = Category::query()
            ->withCount('products')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)->orWhere('slug', 'like', $term);
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->authorize('create', Category::class);

        return view('admin.categories.create', [
            'category' => new Category(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        Category::query()->create($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category created.');
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('admin.categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->products()->exists()) {
            $category->update(['is_active' => false]);

            return redirect()
                ->route('admin.categories.index')
                ->with('status', 'Category has products, so it was deactivated instead of deleted.');
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category deleted.');
    }
}
