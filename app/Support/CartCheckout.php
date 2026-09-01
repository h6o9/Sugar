<?php

namespace App\Support;

class CartCheckout
{
    public static function logoUrl(): string
    {
        return asset('public/img/logo.png');
    }

    public static function companyName(): string
    {
        return 'Sugar Pappi';
    }

    public static function fulfillmentDetails($item, $order = null): array
    {
        $orderType = is_object($order) ? ($order->order_type ?? null) : $order;
        $isWholesale = false;
        if (is_object($order) && method_exists($order, 'channelKey')) {
            $isWholesale = $order->channelKey() === 'wholesale';
        } elseif (in_array(strtolower((string) $orderType), ['wholesale', 'dessert_wholesale', 'dessert-wholesale'], true)) {
            $isWholesale = true;
        }

        if (is_array($item)) {
            $status = (int) ($item['delivery_status'] ?? 1);
            $deliveryAddress = $item['delivery_address'] ?? null;
            $pickupAddress = $item['pickup_address'] ?? $item['branch_location'] ?? null;
            $pickupName = $item['branch_name'] ?? self::companyName();
            $branchId = $item['branch_id'] ?? null;
        } else {
            $status = (int) ($item->delivery_status ?? 1);
            $deliveryAddress = $item->delivery_address ?? null;
            $branch = $item->branch ?? null;
            $pickupAddress = $branch->location ?? null;
            $pickupName = $branch->name ?? self::companyName();
            $branchId = $item->branch_id ?? optional($branch)->id;
        }

        $isDelivery = $isWholesale || $status === 2;
        $method = $isWholesale ? 'wholesale' : ($isDelivery ? 'delivery' : 'pickup');
        $label = self::fulfillmentLabel($item, $orderType);
        $displayAddress = $isDelivery ? $deliveryAddress : $pickupAddress;
        if (!$displayAddress) {
            $displayAddress = $pickupAddress ?: $deliveryAddress;
        }

        return [
            'method' => $method,
            'label' => $label,
            'is_delivery' => $isDelivery,
            'is_pickup' => !$isDelivery,
            'display_address' => $displayAddress,
            'pickup_address' => $pickupAddress,
            'pickup_name' => $pickupName,
            'delivery_address' => $deliveryAddress,
            'branch_id' => $branchId,
            'delivery_status' => $isDelivery ? 2 : 1,
        ];
    }

    public static function fulfillmentLabel($item, $orderType = null): string
    {
        $type = strtolower((string) $orderType);
        if (is_array($item)) {
            $fulfillment = strtolower((string) ($item['fulfillment'] ?? ''));
            $status = (int) ($item['delivery_status'] ?? 1);
        } else {
            $fulfillment = strtolower((string) ($item->fulfillment ?? ''));
            $status = (int) ($item->delivery_status ?? 1);
        }
        if ($type === 'wholesale' || $fulfillment === 'wholesale') {
            return 'Wholesale Delivery';
        }
        if ($status === 2 || $fulfillment === 'home_delivery') {
            return 'Home Delivery';
        }
        return 'Store Pickup';
    }

    public static function selected(): array
    {
        $cart = session('cart', []);
        if (!is_array($cart) || $cart === []) {
            return [];
        }
        $keys = session('checkout_keys');
        if (!is_array($keys) || $keys === []) {
            return $cart;
        }
        $out = [];
        foreach ($keys as $key) {
            if (isset($cart[$key])) {
                $out[$key] = $cart[$key];
            }
        }
        return $out;
    }

    public static function forgetPlaced(): void
    {
        $keys = session('checkout_keys');
        $cart = session('cart', []);
        if (is_array($keys) && $keys !== [] && is_array($cart)) {
            foreach ($keys as $key) {
                unset($cart[$key]);
            }
            session(['cart' => $cart]);
        } else {
            session()->forget('cart');
        }
        session()->forget('checkout_keys');
    }
}
