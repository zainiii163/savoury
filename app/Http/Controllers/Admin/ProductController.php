<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Addon;
use App\Models\Category;
use App\Models\Product;
use App\Services\Menu\ProductWriter;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductWriter $writer,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::query()
            ->with('category')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)->orWhere('slug', 'like', $term);
                });
            })
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('admin.products.create', $this->formData(new Product([
            'is_active' => true,
            'is_available' => true,
            'is_featured' => false,
            'sort_order' => 0,
            'price_minor' => 0,
        ])));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $payload = $request->menuPayload();
        $data = $request->safe()->except(['price_pkr', 'image', 'remove_image', 'addon_ids', 'variants']);
        $data['price_minor'] = $payload['price_minor'];
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $this->writer->create(
            $data,
            $payload['variants'],
            array_map('intval', $request->input('addon_ids', [])),
            $request->file('image'),
        );

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $product->load(['variants', 'addons']);

        return view('admin.products.edit', $this->formData($product));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $payload = $request->menuPayload();
        $data = $request->safe()->except(['price_pkr', 'image', 'remove_image', 'addon_ids', 'variants']);
        $data['price_minor'] = $payload['price_minor'];
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $this->writer->update(
            $product,
            $data,
            $payload['variants'],
            array_map('intval', $request->input('addon_ids', [])),
            $request->file('image'),
            $request->boolean('remove_image'),
        );

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $product->update([
            'is_active' => false,
            'is_available' => false,
        ]);
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product deactivated and archived.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Product $product): array
    {
        $variantRows = $product->variants->map(fn ($variant) => [
            'name' => $variant->name,
            'price_pkr' => Money::toMajor((int) $variant->price_minor),
            'is_default' => (bool) $variant->is_default,
            'is_available' => (bool) $variant->is_available,
        ])->values()->all();

        while (count($variantRows) < 3) {
            $variantRows[] = [
                'name' => '',
                'price_pkr' => '',
                'is_default' => false,
                'is_available' => true,
            ];
        }

        return [
            'product' => $product,
            'categories' => Category::query()->orderBy('sort_order')->orderBy('name')->get(),
            'addons' => Addon::query()->orderBy('sort_order')->orderBy('name')->get(),
            'selectedAddonIds' => $product->addons->pluck('id')->all(),
            'variantRows' => $variantRows,
            'pricePkr' => Money::toMajor((int) ($product->price_minor ?? 0)),
        ];
    }
}
