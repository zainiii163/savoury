<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RestaurantSettingsRequest extends FormRequest
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
        return [
            'restaurant_name' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'ordering_paused' => ['nullable', 'boolean'],
        ];
    }
}
