<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CollectCashRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canCollectCash() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
