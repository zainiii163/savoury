<?php

namespace App\Http\Requests\Admin;

use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageMenu() === true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => Str::slug((string) $this->input('name')),
            ]);
        }

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_available' => $this->boolean('is_available'),
            'is_featured' => $this->boolean('is_featured'),
            'remove_image' => $this->boolean('remove_image'),
            'addon_ids' => array_values(array_filter((array) $this->input('addon_ids', []))),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'required',
                'string',
                'max:170',
                'alpha_dash',
                Rule::unique('products', 'slug')->ignore($productId),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'price_pkr' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
            'is_available' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['integer', 'exists:addons,id'],
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['nullable', 'string', 'max:100'],
            'variants.*.price_pkr' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'variants.*.is_default' => ['sometimes', 'boolean'],
            'variants.*.is_available' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array{price_minor: int, variants: list<array{name: string, price_minor: int, is_default: bool, is_available: bool, sort_order: int}>}
     */
    public function menuPayload(): array
    {
        $variants = [];

        foreach (array_values((array) $this->input('variants', [])) as $index => $variant) {
            $name = trim((string) ($variant['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $variants[] = [
                'name' => $name,
                'price_minor' => Money::toMinor($variant['price_pkr'] ?? 0),
                'is_default' => (bool) ($variant['is_default'] ?? false),
                'is_available' => array_key_exists('is_available', $variant)
                    ? (bool) $variant['is_available']
                    : true,
                'sort_order' => $index + 1,
            ];
        }

        if ($variants !== [] && ! collect($variants)->contains(fn ($variant) => $variant['is_default'])) {
            $variants[0]['is_default'] = true;
        }

        return [
            'price_minor' => Money::toMinor($this->input('price_pkr')),
            'variants' => $variants,
        ];
    }
}
