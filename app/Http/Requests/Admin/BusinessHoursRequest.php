<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BusinessHoursRequest extends FormRequest
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
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'hours.*.is_closed' => ['nullable', 'boolean'],
            'hours.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.closes_at' => ['nullable', 'date_format:H:i'],
        ];
    }
}
