<?php

namespace App\Support;

use App\Models\AddToCartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApiIdExplainer
{
    public static function received(Request $request): array
    {
        $keys = [
            'order_id', 'orderId', 'order', 'id',
            'product_id', 'productId',
            'item_id', 'itemId', 'item_ids', 'itemIds',
            'variant_id', 'channel',
        ];
        $out = [];
        foreach ($keys as $key) {
            if ($request->exists($key) && $request->input($key) !== null && $request->input($key) !== '') {
                $out[$key] = $request->input($key);
            }
        }
        $items = $request->input('items');
        if (is_array($items) && $items !== []) {
            $out['items_product_ids'] = array_values(array_filter(array_map(function ($row) {
                return is_array($row) ? ($row['product_id'] ?? $row['id'] ?? null) : null;
            }, $items)));
        }

        return $out;
    }

    /**
     * @return array{value:string,plain:string,matches:array}
     */
    public static function explain($value, ?int $userId = null): array
    {
        $raw = trim((string) $value);
        $matches = [];
        if ($raw === '') {
            return [
                'value' => $raw,
                'plain' => 'No id was sent.',
                'matches' => [],
            ];
        }

        $product = Product::query()->where('id', $raw)->first()
            ?: (ctype_digit($raw) ? Product::query()->where('id', (int) $raw)->first() : null);
        if ($product) {
            $matches[] = 'PRODUCT "' . $product->name . '" (products.id = ' . $product->id . ')';
        }

        $orderById = Order::query()->where('id', $raw)->first()
            ?: (ctype_digit($raw) ? Order::query()->where('id', (int) $raw)->first() : null);
        if ($orderById) {
            $mine = $userId && (int) $orderById->user_id === (int) $userId;
            $matches[] = 'ORDER #' . $orderById->code . ' (orders.id = ' . $orderById->id . ', user_id ' . $orderById->user_id . ')'
                . ($mine ? ' — this login owns it' : ' — this login does NOT own it');
        }

        $orderByCode = Order::query()->where('code', $raw)->first()
            ?: (ctype_digit($raw) ? Order::query()->where('code', (int) $raw)->first() : null);
        if ($orderByCode && (!$orderById || (int) $orderByCode->id !== (int) $orderById->id)) {
            $mine = $userId && (int) $orderByCode->user_id === (int) $userId;
            $matches[] = 'ORDER CODE ' . $orderByCode->code . ' (orders.id = ' . $orderByCode->id . ')'
                . ($mine ? ' — this login owns it' : ' — this login does NOT own it');
        }

        $line = OrderItem::query()->where('id', $raw)->first()
            ?: (ctype_digit($raw) ? OrderItem::query()->where('id', (int) $raw)->first() : null);
        if ($line) {
            $matches[] = 'ORDER LINE "' . $line->product_name . '" (order_items.id / item_id = ' . $line->id
                . ', product_id = ' . $line->product_id . ', order_id = ' . $line->order_id . ')';
        }

        if (Schema::hasTable('add_to_cart_items')) {
            $cart = AddToCartItem::query()->where('id', $raw)->first()
                ?: (ctype_digit($raw) ? AddToCartItem::query()->where('id', (int) $raw)->first() : null);
            if ($cart) {
                $matches[] = 'CART ROW "' . $cart->product_name . '" (add_to_cart_items.id = ' . $cart->id . ') — not an order';
            }
        }

        if (Schema::hasTable('product_variants') && ctype_digit($raw)) {
            $variant = DB::table('product_variants')->where('id', (int) $raw)->first();
            if ($variant) {
                $matches[] = 'VARIANT size "' . ($variant->size ?? '') . '" (product_variants.id = ' . $variant->id
                    . ', product_id = ' . $variant->product_id . ')';
            }
        }

        if ($matches === []) {
            $plain = $raw . ' was not found as an order id, order number (code), product, order line (item_id), cart row, or variant.';
        } else {
            $plain = $raw . ' is: ' . implode(' | ', $matches) . '.';
        }

        return [
            'value' => $raw,
            'plain' => $plain,
            'matches' => $matches,
        ];
    }

    public static function payload(Request $request, string $code, string $message, $confusedValue = null): array
    {
        $userId = $request->user() ? (int) $request->user()->id : null;
        $body = [
            'status' => false,
            'code' => $code,
            'message' => $message,
            'you_sent' => self::received($request),
            'how_to_add_item' => [
                'url' => 'POST /api/orders/add-items',
                'body' => [
                    'order_id' => 'GET /api/orders → data.all_orders[].id (orders.id, e.g. 673)',
                    'channel' => 'same as the order: regular | special | drive_in | wholesale',
                    'items' => [[
                        'product_id' => 'products.id (e.g. 336 Sticky Toffee)',
                        'quantity' => 1,
                        'price' => 'menu price, not 0',
                    ]],
                ],
            ],
            'how_to_edit_item' => [
                'url' => 'POST /api/orders/update-item',
                'body' => [
                    'order_id' => 'orders.id e.g. 673',
                    'item_id' => 'GET /api/orders/{id} → items[].id (order_items.id, e.g. 804) — NOT products.id',
                    'quantity' => 2,
                ],
            ],
            'ids' => [
                'order_id' => 'orders.id from GET /api/orders → all_orders[].id',
                'order_number' => 'orders.code (the long number on the receipt). You may send this as order_id too.',
                'product_id' => 'products.id — only inside items[] when ADDING a product',
                'item_id' => 'order_items.id — only when EDITING/REMOVING a line already on the order',
            ],
        ];
        if ($confusedValue !== null && $confusedValue !== '') {
            $body['this_number'] = self::explain($confusedValue, $userId);
            $body['message'] = trim($message . ' ' . $body['this_number']['plain']);
        }

        return $body;
    }
}
