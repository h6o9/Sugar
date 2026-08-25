<?php

namespace App\Support;

class CartCheckout
{
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
        return 'Takeaway';
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
