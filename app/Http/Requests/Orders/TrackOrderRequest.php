<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class TrackOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'public_reference' => strtoupper(trim((string) $this->input('public_reference'))),
            'tracking_token' => trim((string) $this->input('tracking_token')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'public_reference' => ['required', 'string', 'max:40'],
            'tracking_token' => ['required', 'string', 'min:20', 'max:128'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tracking_token.required' => 'Paste the tracking token from your order confirmation.',
        ];
    }
}
