<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddonRequest;
use App\Models\Addon;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddonController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Addon::class);

        $addons = Addon::query()
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

        return view('admin.addons.index', compact('addons'));
    }

    public function create(): View
    {
        $this->authorize('create', Addon::class);

        return view('admin.addons.create', [
            'addon' => new Addon(['is_active' => true, 'sort_order' => 0, 'price_minor' => 0]),
            'pricePkr' => 0,
        ]);
    }

    public function store(AddonRequest $request): RedirectResponse
    {
        $this->authorize('create', Addon::class);

        $data = $request->safe()->except(['price_pkr']);
        $data['price_minor'] = $request->priceMinor();
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        Addon::query()->create($data);

        return redirect()
            ->route('admin.addons.index')
            ->with('status', 'Add-on created.');
    }

    public function edit(Addon $addon): View
    {
        $this->authorize('update', $addon);

        return view('admin.addons.edit', [
            'addon' => $addon,
            'pricePkr' => Money::toMajor((int) $addon->price_minor),
        ]);
    }

    public function update(AddonRequest $request, Addon $addon): RedirectResponse
    {
        $this->authorize('update', $addon);

        $data = $request->safe()->except(['price_pkr']);
        $data['price_minor'] = $request->priceMinor();
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $addon->update($data);

        return redirect()
            ->route('admin.addons.index')
            ->with('status', 'Add-on updated.');
    }

    public function destroy(Addon $addon): RedirectResponse
    {
        $this->authorize('delete', $addon);

        $addon->update(['is_active' => false]);
        $addon->delete();

        return redirect()
            ->route('admin.addons.index')
            ->with('status', 'Add-on deactivated and archived.');
    }
}
