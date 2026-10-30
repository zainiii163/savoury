<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Services\Cart\SessionCartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly SessionCartService $cart,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $zoneId = $request->filled('delivery_zone_id')
            ? $request->integer('delivery_zone_id')
            : null;

        return response()->json([
            'data' => $this->cart->totals($zoneId)->toArray(),
        ]);
    }

    public function store(AddCartItemRequest $request): JsonResponse
    {
        $this->cart->add(
            (int) $request->integer('product_id'),
            $request->filled('product_variant_id') ? $request->integer('product_variant_id') : null,
            array_map('intval', $request->input('addon_ids', [])),
            $request->integer('quantity', 1),
        );

        return response()->json([
            'message' => 'Added to cart.',
            'data' => $this->cart->totals()->toArray(),
        ], 201);
    }

    public function update(UpdateCartItemRequest $request, string $key): JsonResponse
    {
        $this->cart->updateQuantity($key, $request->integer('quantity'));

        return response()->json([
            'message' => 'Cart updated.',
            'data' => $this->cart->totals()->toArray(),
        ]);
    }

    public function destroy(string $key): JsonResponse
    {
        $this->cart->remove($key);

        return response()->json([
            'message' => 'Item removed.',
            'data' => $this->cart->totals()->toArray(),
        ]);
    }
}
