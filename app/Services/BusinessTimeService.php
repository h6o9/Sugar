<?php

namespace App\Services;

use App\Models\BusinessSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BusinessTimeService
{
    public function timezone(): string
    {
        return BusinessSetting::getValue('business_timezone', 'Europe/London') ?: 'Europe/London';
    }

    public function now(): Carbon
    {
        $timezone = $this->timezone();

        try {
            $payload = $this->googleTimezonePayload();
            if (!empty($payload['timeZoneId'])) {
                $timezone = $payload['timeZoneId'];
            }
            if (isset($payload['rawOffset'], $payload['dstOffset'])) {
                $utc = Carbon::now('UTC');
                return $utc->copy()->addSeconds((int) $payload['rawOffset'] + (int) $payload['dstOffset'])->setTimezone($timezone);
            }
        } catch (\Throwable $e) {
            Log::warning('BusinessTimeService: Google timezone fallback', ['error' => $e->getMessage()]);
        }

        return Carbon::now($timezone);
    }

    public function openingHours(): array
    {
        $hours = BusinessSetting::json('opening_hours', []);
        if (!empty($hours)) {
            return $hours;
        }

        return [
            'monday' => ['open' => '16:00', 'close' => '06:00', 'closes_next_day' => true],
            'tuesday' => ['open' => '16:00', 'close' => '06:00', 'closes_next_day' => true],
            'wednesday' => ['open' => '16:00', 'close' => '06:00', 'closes_next_day' => true],
            'thursday' => ['open' => '16:00', 'close' => '06:00', 'closes_next_day' => true],
            'friday' => ['open' => '16:00', 'close' => '06:00', 'closes_next_day' => true],
            'saturday' => ['open' => '16:00', 'close' => '06:00', 'closes_next_day' => true],
            'sunday' => ['open' => '14:00', 'close' => '06:00', 'closes_next_day' => true],
        ];
    }

    public function status(?Carbon $at = null): array
    {
        $now = $at ? $at->copy()->setTimezone($this->timezone()) : $this->now();
        $period = $this->currentPeriod($now);
        $nextOpen = $this->nextOpening($now);

        $isOpen = $period !== null;
        $message = $isOpen
            ? 'We are open.'
            : 'Shop is currently closed. You can schedule your order for ' . $nextOpen->format('g:i A') . '.';

        if (!$isOpen) {
            $openDay = strtolower($nextOpen->format('l'));
            if ($openDay === 'sunday' && (int) $nextOpen->format('G') < 16) {
                $message = 'Shop is currently closed. You can schedule your order for 2:00 PM.';
            } elseif ((int) $nextOpen->format('G') === 16) {
                $message = 'Shop is currently closed. You can schedule your order for 4:00 PM.';
            }
        }

        return [
            'is_open' => $isOpen,
            'timezone' => $this->timezone(),
            'now' => $now->toIso8601String(),
            'now_display' => $now->format('Y-m-d H:i:s'),
            'message' => $message,
            'next_opening_at' => $nextOpen->toIso8601String(),
            'next_opening_display' => $nextOpen->format('l g:i A'),
            'next_opening_time' => $nextOpen->format('g:i A'),
            'current_period_start' => $period ? $period['start']->toIso8601String() : null,
            'current_period_end' => $period ? $period['end']->toIso8601String() : null,
        ];
    }

    public function isOpen(?Carbon $at = null): bool
    {
        return $this->status($at)['is_open'];
    }

    public function nextOpening(?Carbon $at = null): Carbon
    {
        $now = $at ? $at->copy()->setTimezone($this->timezone()) : $this->now();
        $hours = $this->openingHours();

        for ($i = 0; $i < 8; $i++) {
            $day = $now->copy()->addDays($i)->startOfDay();
            $key = strtolower($day->format('l'));
            $rule = $hours[$key] ?? ['open' => '16:00', 'close' => '06:00', 'closes_next_day' => true];
            $open = $this->timeOnDate($day, $rule['open'] ?? '16:00');
            if ($open->gt($now)) {
                return $open;
            }
        }

        return $now->copy()->addDay()->setTime(16, 0, 0);
    }

    public function currentPeriod(?Carbon $at = null): ?array
    {
        $now = $at ? $at->copy()->setTimezone($this->timezone()) : $this->now();
        $hours = $this->openingHours();

        foreach ([0, 1] as $offset) {
            $day = $now->copy()->subDays($offset)->startOfDay();
            $key = strtolower($day->format('l'));
            $rule = $hours[$key] ?? null;
            if (!$rule) {
                continue;
            }
            $start = $this->timeOnDate($day, $rule['open'] ?? '16:00');
            $end = $this->timeOnDate($day, $rule['close'] ?? '06:00');
            if (!empty($rule['closes_next_day']) || $end->lte($start)) {
                $end = $end->addDay();
            }
            if ($now->gte($start) && $now->lt($end)) {
                return ['start' => $start, 'end' => $end, 'weekday' => $key];
            }
        }

        return null;
    }

    protected function timeOnDate(Carbon $date, string $time): Carbon
    {
        [$h, $m] = array_map('intval', explode(':', $time) + [0, 0]);
        return $date->copy()->setTime($h, $m, 0);
    }

    protected function googleApiKey(): ?string
    {
        $key = env('GOOGLE_MAPS_API_KEY') ?: env('GOOGLE_MAP_KEY');
        return $key ? trim($key) : null;
    }

    protected function googleTimezonePayload(): array
    {
        $key = $this->googleApiKey();
        if (!$key) {
            return [];
        }

        return Cache::remember('google_timezone_payload', 300, function () use ($key) {
            $lat = BusinessSetting::getValue('google_timezone_lat', '53.4808');
            $lng = BusinessSetting::getValue('google_timezone_lng', '-2.2426');
            $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/timezone/json', [
                'location' => $lat . ',' . $lng,
                'timestamp' => time(),
                'key' => $key,
            ]);

            if (!$response->ok()) {
                return [];
            }

            $json = $response->json();
            if (($json['status'] ?? '') !== 'OK') {
                Log::warning('Google Time Zone API status', ['payload' => $json]);
                return [];
            }

            if (!empty($json['timeZoneId'])) {
                BusinessSetting::setValue('business_timezone', $json['timeZoneId']);
            }

            return $json;
        });
    }
}
