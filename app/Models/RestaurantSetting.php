<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();

        if ($setting === null) {
            return $default;
        }

        return match ($setting->type) {
            'bool', 'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'int', 'integer' => (int) $setting->value,
            'json' => json_decode((string) $setting->value, true),
            default => $setting->value,
        };
    }

    public static function setValue(
        string $key,
        mixed $value,
        string $type = 'string',
        string $group = 'general',
        ?string $description = null,
    ): self {
        $stored = match ($type) {
            'bool', 'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => $value === null ? null : (string) $value,
        };

        return static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $stored,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );
    }
}
