<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageOrders() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(config('savoury.order_statuses'))],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
