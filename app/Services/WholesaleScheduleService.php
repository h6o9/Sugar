<?php

namespace App\Services;

use App\Models\BusinessSetting;
use Carbon\Carbon;

class WholesaleScheduleService
{
    /** @var BusinessTimeService */
    protected $time;

    public function __construct(BusinessTimeService $time)
    {
        $this->time = $time;
    }

    public function deliveryDays(): array
    {
        $days = BusinessSetting::json('wholesale_delivery_days', ['monday', 'thursday', 'saturday']);
        return array_map('strtolower', $days);
    }

    public function windowStart(): string
    {
        return BusinessSetting::getValue('wholesale_delivery_start', '19:00') ?: '19:00';
    }

    public function windowEnd(): string
    {
        return BusinessSetting::getValue('wholesale_delivery_end', '22:00') ?: '22:00';
    }

    public function cutoffTime(): string
    {
        return BusinessSetting::getValue('wholesale_cutoff_time', '19:00') ?: '19:00';
    }

    public function availableDates(int $weeks = 8): array
    {
        $now = $this->time->now();
        $days = $this->deliveryDays();
        $cutoff = $this->cutoffTime();
        $dates = [];

        for ($i = 0; $i < ($weeks * 7); $i++) {
            $date = $now->copy()->startOfDay()->addDays($i);
            $weekday = strtolower($date->format('l'));
            if (!in_array($weekday, $days, true)) {
                continue;
            }

            $cutoffAt = $this->cutoffAt($date, $cutoff);
            if ($now->gte($cutoffAt)) {
                continue;
            }

            $dates[] = [
                'date' => $date->toDateString(),
                'label' => $date->format('l j M Y'),
                'weekday' => ucfirst($weekday),
                'window' => $this->windowLabel(),
                'cutoff_at' => $cutoffAt->toIso8601String(),
            ];
        }

        return $dates;
    }

    public function isValidDate(string $date): bool
    {
        foreach ($this->availableDates() as $available) {
            if ($available['date'] === $date) {
                return true;
            }
        }
        return false;
    }

    public function cutoffMessage(): string
    {
        return "Today's wholesale delivery ordering period has closed. Please select the next available delivery date.";
    }

    public function modifyLockHours(): int
    {
        return 6;
    }

    public function lockedMessage(): string
    {
        return 'You can no longer update this order.';
    }

    public function modifyUntil(?string $date): ?Carbon
    {
        if (!$date) {
            return null;
        }
        try {
            $deliveryDay = Carbon::parse($date, $this->time->timezone())->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
        [$h, $m] = array_map('intval', explode(':', $this->windowStart()) + [0, 0]);
        return $deliveryDay->copy()->setTime($h, $m, 0)->subHours($this->modifyLockHours());
    }

    public function windowLabel(): string
    {
        return $this->formatTime($this->windowStart()) . ' – ' . $this->formatTime($this->windowEnd());
    }

    protected function cutoffAt(Carbon $date, string $cutoff): Carbon
    {
        [$h, $m] = array_map('intval', explode(':', $cutoff) + [0, 0]);
        return $date->copy()->setTime($h, $m, 0);
    }

    protected function formatTime(string $time): string
    {
        [$h, $m] = array_map('intval', explode(':', $time) + [0, 0]);
        return Carbon::createFromTime($h, $m, 0, $this->time->timezone())->format('g:i A');
    }
}
