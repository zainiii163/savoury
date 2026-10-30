<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'day_of_week',
        'label',
        'opens_at',
        'closes_at',
        'is_closed',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_closed' => 'boolean',
        ];
    }
}
