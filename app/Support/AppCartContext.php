<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class AppCartContext
{
    public static function key(int $userId): string
    {
        return 'app_cart_ctx_' . $userId;
    }

    public static function get(int $userId): array
    {
        $stored = Cache::get(self::key($userId), []);
        return array_merge([
            'channel' => 'regular',
            'fulfillment' => null,
            'wholesale_delivery_date' => null,
            'adding_to_order_id' => null,
            'pickup_date' => null,
            'pickup_time' => null,
            'checkout_item_ids' => null,
            'is_scheduled' => false,
            'scheduled_at' => null,
        ], is_array($stored) ? $stored : []);
    }

    public static function put(int $userId, array $data): array
    {
        $merged = array_merge(self::get($userId), $data);
        Cache::put(self::key($userId), $merged, now()->addDays(7));
        return $merged;
    }

    public static function forget(int $userId): void
    {
        Cache::forget(self::key($userId));
    }

    public static function clearAddToOrder(int $userId): array
    {
        return self::put($userId, [
            'adding_to_order_id' => null,
            'wholesale_delivery_date' => null,
        ]);
    }

    public static function normalizeChannel(?string $channel): string
    {
        $value = strtolower(trim((string) $channel));
        if (in_array($value, ['wholesale', 'dessert_wholesale', 'dessert-wholesale'], true)) {
            return 'wholesale';
        }
        if (in_array($value, ['drive_in', 'drive-in', 'drivein'], true)) {
            return 'drive_in';
        }
        if (in_array($value, ['special', 'pappi_special', 'pappi-special', 'sugar_pappi_special'], true)) {
            return 'special';
        }
        return 'regular';
    }

    public static function channelLabel(string $channel): string
    {
        switch (self::normalizeChannel($channel)) {
            case 'wholesale':
                return 'Dessert Wholesale';
            case 'drive_in':
                return 'Drive-In';
            case 'special':
                return 'Sugar Papi Special';
            default:
                return 'Regular Order';
        }
    }
}
