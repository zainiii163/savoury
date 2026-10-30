<?php

namespace App\Services\Checkout;

use App\Models\BusinessHour;
use App\Models\RestaurantSetting;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class OrderingGate
{
    public function assertOpenForOrders(): void
    {
        if (filter_var(RestaurantSetting::getValue('ordering_paused', false), FILTER_VALIDATE_BOOLEAN)) {
            throw ValidationException::withMessages([
                'ordering' => 'Ordering is temporarily paused. Please try again later or call the restaurant.',
            ]);
        }

        $hours = BusinessHour::query()->orderBy('day_of_week')->get();

        if ($hours->isEmpty()) {
            // Owner has not supplied hours yet — do not invent closures.
            return;
        }

        $now = Carbon::now(config('savoury.display_timezone'));
        $today = $hours->firstWhere('day_of_week', $now->dayOfWeek);

        if ($today === null || $today->is_closed || $today->opens_at === null || $today->closes_at === null) {
            throw ValidationException::withMessages([
                'ordering' => 'The restaurant is closed right now.',
            ]);
        }

        $open = Carbon::parse($now->toDateString().' '.$today->opens_at, $now->timezone);
        $close = Carbon::parse($now->toDateString().' '.$today->closes_at, $now->timezone);

        if ($now->lt($open) || $now->gte($close)) {
            throw ValidationException::withMessages([
                'ordering' => 'The restaurant is closed right now.',
            ]);
        }
    }
}
