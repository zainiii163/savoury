<?php

namespace App\Http\Requests\Admin;

use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AddonRequest extends FormRequest
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
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $addonId = $this->route('addon')?->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required',
                'string',
                'max:120',
                'alpha_dash',
                Rule::unique('addons', 'slug')->ignore($addonId),
            ],
            'price_pkr' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function priceMinor(): int
    {
        return Money::toMinor($this->input('price_pkr'));
    }
}
