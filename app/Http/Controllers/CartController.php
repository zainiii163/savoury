<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Services\Cart\SessionCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly SessionCartService $cart,
    ) {
    }

    public function show(): View
    {
        return view('cart.show', [
            'totals' => $this->cart->totals(),
            'brand' => config('savoury.brand'),
        ]);
    }

    public function store(AddCartItemRequest $request): RedirectResponse
    {
        $this->cart->add(
            (int) $request->integer('product_id'),
            $request->filled('product_variant_id') ? $request->integer('product_variant_id') : null,
            array_map('intval', $request->input('addon_ids', [])),
            $request->integer('quantity', 1),
        );

        return redirect()
            ->route('cart.show')
            ->with('status', 'Added to cart.');
    }

    public function update(UpdateCartItemRequest $request, string $key): RedirectResponse
    {
        $this->cart->updateQuantity($key, $request->integer('quantity'));

        return redirect()
            ->route('cart.show')
            ->with('status', 'Cart updated.');
    }

    public function destroy(string $key): RedirectResponse
    {
        $this->cart->remove($key);

        return redirect()
            ->route('cart.show')
            ->with('status', 'Item removed.');
    }
}
