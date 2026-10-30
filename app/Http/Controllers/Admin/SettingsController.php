<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BusinessHoursRequest;
use App\Http\Requests\Admin\RestaurantSettingsRequest;
use App\Models\BusinessHour;
use App\Models\RestaurantSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const DAY_LABELS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public function edit(): View
    {
        $existing = BusinessHour::query()->get()->keyBy('day_of_week');
        $hours = [];

        foreach (self::DAY_LABELS as $day => $label) {
            $row = $existing->get($day);
            $hours[] = [
                'day_of_week' => $day,
                'label' => $label,
                'is_closed' => (bool) ($row?->is_closed ?? false),
                'opens_at' => $row?->opens_at ? substr((string) $row->opens_at, 0, 5) : '',
                'closes_at' => $row?->closes_at ? substr((string) $row->closes_at, 0, 5) : '',
            ];
        }

        return view('admin.settings.edit', [
            'settings' => [
                'restaurant_name' => RestaurantSetting::getValue('restaurant_name', 'Savoury Spot'),
                'phone' => RestaurantSetting::getValue('phone', config('savoury.brand.phone')),
                'address' => RestaurantSetting::getValue('address', ''),
                'ordering_paused' => (bool) RestaurantSetting::getValue('ordering_paused', false),
            ],
            'hours' => $hours,
        ]);
    }

    public function update(RestaurantSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        RestaurantSetting::setValue('restaurant_name', $data['restaurant_name'] ?? null, 'string', 'restaurant');
        RestaurantSetting::setValue('phone', $data['phone'] ?? null, 'string', 'restaurant');
        RestaurantSetting::setValue('address', $data['address'] ?? null, 'string', 'restaurant');
        RestaurantSetting::setValue(
            'ordering_paused',
            $request->boolean('ordering_paused'),
            'bool',
            'ordering',
            'When true, checkout is blocked.'
        );

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Restaurant settings saved.');
    }

    public function updateHours(BusinessHoursRequest $request): RedirectResponse
    {
        foreach ($request->validated('hours') as $row) {
            $closed = (bool) ($row['is_closed'] ?? false);

            BusinessHour::query()->updateOrCreate(
                ['day_of_week' => (int) $row['day_of_week']],
                [
                    'label' => self::DAY_LABELS[(int) $row['day_of_week']] ?? null,
                    'is_closed' => $closed,
                    'opens_at' => $closed ? null : ($row['opens_at'] ?: null),
                    'closes_at' => $closed ? null : ($row['closes_at'] ?: null),
                ]
            );
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Business hours saved. Empty hours previously meant always open; configured hours now apply.');
    }
}
