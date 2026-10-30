<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageSettings() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $zoneId = $this->route('delivery_zone')?->id;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('delivery_zones', 'name')->ignore($zoneId)],
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', Rule::unique('delivery_zones', 'slug')->ignore($zoneId)],
            'delivery_fee_pkr' => ['required', 'numeric', 'min:0'],
            'min_order_pkr' => ['nullable', 'numeric', 'min:0'],
            'coverage_notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
