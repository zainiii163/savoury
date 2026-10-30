<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeliveryZoneRequest;
use App\Models\DeliveryZone;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeliveryZoneController extends Controller
{
    public function index(): View
    {
        return view('admin.delivery-zones.index', [
            'zones' => DeliveryZone::query()->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.delivery-zones.create');
    }

    public function store(DeliveryZoneRequest $request): RedirectResponse
    {
        $data = $this->payload($request);

        DeliveryZone::query()->create($data);

        return redirect()
            ->route('admin.delivery-zones.index')
            ->with('status', 'Delivery zone created.');
    }

    public function edit(DeliveryZone $deliveryZone): View
    {
        return view('admin.delivery-zones.edit', [
            'zone' => $deliveryZone,
        ]);
    }

    public function update(DeliveryZoneRequest $request, DeliveryZone $deliveryZone): RedirectResponse
    {
        $deliveryZone->update($this->payload($request));

        return redirect()
            ->route('admin.delivery-zones.index')
            ->with('status', 'Delivery zone updated.');
    }

    public function destroy(DeliveryZone $deliveryZone): RedirectResponse
    {
        $deliveryZone->delete();

        return redirect()
            ->route('admin.delivery-zones.index')
            ->with('status', 'Delivery zone archived.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(DeliveryZoneRequest $request): array
    {
        $validated = $request->validated();
        $slug = filled($validated['slug'] ?? null)
            ? $validated['slug']
            : Str::slug($validated['name']);

        return [
            'name' => $validated['name'],
            'slug' => $slug,
            'delivery_fee_minor' => Money::toMinor($validated['delivery_fee_pkr']),
            'min_order_minor' => Money::toMinor($validated['min_order_pkr'] ?? 0),
            'coverage_notes' => $validated['coverage_notes'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
