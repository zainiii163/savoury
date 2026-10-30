<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['integer', 'exists:addons,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
