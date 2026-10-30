<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('idempotency_key')) {
            $this->merge([
                'idempotency_key' => (string) Str::uuid(),
            ]);
        }

        $this->merge([
            'phone' => preg_replace('/\s+/', '', (string) $this->input('phone')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $fulfillment = $this->input('fulfillment_type');

        return [
            'customer_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^(03\d{9}|\+923\d{9}|923\d{9})$/'],
            'fulfillment_type' => ['required', Rule::in(config('savoury.fulfillment_types'))],
            'payment_method' => ['required', Rule::in(config('savoury.payment_methods'))],
            'delivery_zone_id' => [
                Rule::requiredIf($fulfillment === 'delivery'),
                'nullable',
                'integer',
                'exists:delivery_zones,id',
            ],
            'address' => [
                Rule::requiredIf($fulfillment === 'delivery'),
                'nullable',
                'string',
                'max:1000',
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
            'idempotency_key' => ['required', 'string', 'max:64'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid Pakistan mobile number (e.g. 03166100080).',
        ];
    }
}
