<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemToppings;
use App\Models\OrderModification;
use App\Models\OrderPayment;
use App\Models\OrderReceipt;
use App\Models\Product;
use App\Models\Topping;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class OrderLifecycleService
{
    /** @var BusinessTimeService */
    protected $time;
    /** @var WholesaleScheduleService */
    protected $wholesale;

    public function __construct(BusinessTimeService $time, WholesaleScheduleService $wholesale)
    {
        $this->time = $time;
        $this->wholesale = $wholesale;
    }

    public function addToOrderMinutes(): int
    {
        $raw = BusinessSetting::getValue('add_to_order_minutes', 10);
        $minutes = (int) ($raw ?: 10);
        // #region agent log
        file_put_contents(base_path('debug-1796d5.log'), json_encode([
            'sessionId' => '1796d5',
            'hypothesisId' => 'B',
            'location' => 'OrderLifecycleService.php:addToOrderMinutes',
            'message' => 'add_to_order_minutes from settings',
            'data' => ['raw' => $raw, 'minutes' => $minutes],
            'timestamp' => (int) round(microtime(true) * 1000),
        ]) . "\n", FILE_APPEND);
        // #endregion
        return $minutes;
    }

    public function driveInPercent(): float
    {
        return (float) (BusinessSetting::getValue('drive_in_discount_percent', 20) ?: 20);
    }

    public function isWholesale($order): bool
    {
        if (is_object($order) && !empty($order->wholesale_delivery_date)) {
            return true;
        }
        $type = is_object($order) ? ($order->order_type ?? $order->menu_type ?? '') : (string) $order;
        return in_array(strtolower((string) $type), ['wholesale', 'dessert_wholesale', 'dessert-wholesale'], true);
    }

    public function initializeNewOrder(Order $order, array $meta = []): Order
    {
        $now = $this->time->now();
        $orderType = $meta['order_type'] ?? $order->order_type ?? 'standard';
        $menuType = $meta['menu_type'] ?? $order->menu_type ?? 'food';

        if (Schema::hasColumn('orders', 'order_type')) {
            $order->order_type = $orderType;
        }
        if (Schema::hasColumn('orders', 'menu_type')) {
            $order->menu_type = $menuType;
        }
        if (Schema::hasColumn('orders', 'last_modified_at')) {
            $order->last_modified_at = $now->copy()->utc();
        }

        $isWholesale = $this->isWholesale($orderType) || $this->isWholesale($menuType);
        if (!$isWholesale && empty($meta['is_scheduled']) && !$this->time->isOpen()) {
            $meta['is_scheduled'] = true;
            $meta['scheduled_at'] = $this->time->nextOpening();
        }

        if (!empty($meta['wholesale_delivery_date']) && Schema::hasColumn('orders', 'wholesale_delivery_date')) {
            $order->wholesale_delivery_date = $meta['wholesale_delivery_date'];
            $isWholesale = true;
        }

        $this->startAddWindow($order, $isWholesale);

        if (!empty($meta['is_scheduled']) && Schema::hasColumn('orders', 'is_scheduled')) {
            $order->is_scheduled = true;
            $order->scheduled_at = $meta['scheduled_at'] ?? null;
            if ($order->scheduled_at) {
                $order->status = 'Scheduled';
            }
        }

        $this->applyDriveInDiscount($order, $orderType);
        $this->syncPaidAmount($order);
        $order->save();

        try {
            $this->generateReceipt($order, 'created');
        } catch (\Throwable $e) {
            Log::warning('Receipt create failed; timer window kept', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        $this->rememberActiveOrder($order);
        return $order->fresh() ?: $order;
    }

    public function startAddWindow(Order $order, ?bool $isWholesale = null): void
    {
        if (!Schema::hasColumn('orders', 'add_items_until')) {
            return;
        }
        $wholesale = $isWholesale ?? $this->isWholesale($order);
        if ($wholesale) {
            $until = $this->wholesaleModifyDeadline($order);
            if ($until) {
                $order->add_items_until = $until->copy()->utc();
                return;
            }
            $base = $order->getRawOriginal('created_at') ?: $order->created_at ?: $this->utcNow();
            $order->add_items_until = $this->asCarbonUtc($base)->addDays(7);
            return;
        }
        $order->add_items_until = $this->deadlineUtc();
    }

    public function rememberActiveOrder(Order $order): void
    {
        if (!$order->id || !$order->add_items_until || $this->isWholesale($order)) {
            return;
        }
        Session::put('active_add_order_id', $order->id);
    }

    public function hasActiveAddToOrderSession(?int $userId = null): bool
    {
        $id = Session::get('adding_to_order_id');
        if (!$id) {
            return false;
        }
        $query = Order::query()->where('id', $id);
        if ($userId) {
            $query->where('user_id', $userId);
        }
        $order = $query->first();
        if ($order && $this->canModify($order)) {
            return true;
        }
        Session::forget('adding_to_order_id');
        return false;
    }

    public function cancelWholesaleAddToOrderSession(): void
    {
        $id = Session::get('adding_to_order_id');
        if (!$id) {
            return;
        }
        $order = Order::find($id);
        if (!$order || !$this->isWholesale($order)) {
            return;
        }
        Session::forget('adding_to_order_id');
        if (session('selected_order_type') === 'wholesale') {
            Session::put('selected_order_type', 'standard');
        }
        Session::forget('wholesale_delivery_date');
    }

    public function modifyDeadline(Order $order): ?Carbon
    {
        if ($this->isWholesale($order)) {
            $until = $this->wholesaleModifyDeadline($order);
            if ($until) {
                return $until;
            }
            if (Schema::hasColumn('orders', 'add_items_until')) {
                $stored = $order->getRawOriginal('add_items_until') ?: $order->add_items_until;
                if ($stored) {
                    return $this->asCarbonUtc($stored);
                }
            }
            $base = $order->getRawOriginal('created_at') ?: $order->created_at;
            return $base ? $this->asCarbonUtc($base)->addDays(7) : null;
        }
        $until = null;
        if (Schema::hasColumn('orders', 'add_items_until')) {
            $until = $order->getRawOriginal('add_items_until') ?: $order->add_items_until;
        }
        if (!$until) {
            $base = $order->getRawOriginal('last_modified_at')
                ?: $order->getRawOriginal('created_at')
                ?: $order->created_at;
            if (!$base) {
                return null;
            }
            return $this->asCarbonUtc($base)->addMinutes($this->addToOrderMinutes());
        }
        return $this->asCarbonUtc($until);
    }

    public function wholesaleModifyDeadline(Order $order): ?Carbon
    {
        $date = $order->wholesale_delivery_date ?? null;
        if (!$date) {
            return null;
        }
        $until = $this->wholesale->modifyUntil((string) $date);
        if (!$until) {
            return null;
        }
        return $until->copy()->utc();
    }

    public function canModify(Order $order): bool
    {
        if (in_array($order->status, ['Delivered', 'Cancelled', 'Canceled', 'completed'], true)) {
            return false;
        }
        $until = $this->modifyDeadline($order);
        if (!$until) {
            return false;
        }
        return $this->utcNow()->lt($until);
    }

    public function remainingSeconds(Order $order): int
    {
        $until = $this->modifyDeadline($order);
        if (!$until) {
            return 0;
        }
        return max(0, $this->utcNow()->diffInSeconds($until, false));
    }

    public function publicState(?Order $order): array
    {
        if (!$order) {
            return [
                'current_order' => null,
                'can_add_items' => false,
                'add_items_until' => null,
                'remaining_seconds' => 0,
                'receipt_version' => null,
                'is_scheduled' => false,
                'scheduled_time' => null,
                'order_type' => null,
                'wholesale_delivery_date' => null,
            ];
        }

        $canAdd = $this->canModify($order);
        $isWholesale = $this->isWholesale($order);
        $lockLocal = null;
        if ($isWholesale) {
            $lock = $this->wholesaleModifyDeadline($order);
            $lockLocal = $lock ? $lock->copy()->setTimezone($this->time->timezone())->format('g:i A, l j M Y') : null;
        }
        if ($isWholesale) {
            $message = $canAdd
                ? 'You can add or remove items until 6 hours before your wholesale delivery time' . ($lockLocal ? ' (by ' . $lockLocal . ')' : '') . '.'
                : $this->wholesale->lockedMessage();
        } else {
            $message = $canAdd
                ? 'You have ' . $this->addToOrderMinutes() . ' minutes to add or remove items. Any change cancels the old receipt, issues a new one, and restarts this timer. If you do nothing, the order stays placed.'
                : 'Your time to change this order has ended. The order stays as placed.';
        }
        $payload = [
            'current_order' => [
                'id' => $order->id,
                'code' => $order->code,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
            ],
            'can_add_items' => $canAdd,
            'add_items_until' => $order->add_items_until,
            'remaining_seconds' => $this->remainingSeconds($order),
            'remaining_time' => $this->formatRemaining($this->remainingSeconds($order)),
            'receipt_version' => $order->receipt_version ?? 1,
            'is_scheduled' => (bool) ($order->is_scheduled ?? false),
            'scheduled_time' => $order->scheduled_at,
            'order_type' => $order->order_type,
            'wholesale_delivery_date' => $order->wholesale_delivery_date,
            'is_wholesale' => $isWholesale,
            'channel' => $order->channelKey(),
            'channel_label' => $order->channelLabel(),
            'lock_at_label' => $lockLocal,
            'message' => $message,
        ];
        if (!$isWholesale) {
            $payload['add_minutes'] = $this->addToOrderMinutes();
        }

        return $payload;
    }

    public function timerPayload(Order $order): array
    {
        $minutes = $this->addToOrderMinutes();
        $until = $this->modifyDeadline($order);
        $startRaw = $order->getRawOriginal('last_modified_at')
            ?: $order->last_modified_at
            ?: $order->getRawOriginal('created_at')
            ?: $order->created_at;
        $started = $startRaw ? $this->asCarbonUtc($startRaw) : null;
        $remaining = $this->remainingSeconds($order);
        $running = $this->canModify($order) && $remaining > 0;
        $tz = $this->time->timezone();
        $isWholesale = $this->isWholesale($order);

        if ($isWholesale) {
            $message = $running
                ? 'You can add or remove items until 6 hours before your wholesale delivery time.'
                : $this->wholesale->lockedMessage();
        } else {
            $message = $running
                ? 'Your order is placed. You have ' . $minutes . ' minutes to add items, remove items, or change size and toppings. Any change restarts this timer.'
                : 'Your time to change this order has ended. The order stays as placed.';
        }

        $payload = [
            'started_at' => $started ? $started->toIso8601String() : null,
            'started_at_label' => $started ? $started->copy()->setTimezone($tz)->format('g:i A') : null,
            'started_at_unix' => $started ? $started->timestamp : null,
            'ends_at' => $until ? $until->toIso8601String() : null,
            'ends_at_label' => $until ? $until->copy()->setTimezone($tz)->format('g:i A') : null,
            'ends_at_unix' => $until ? $until->timestamp : null,
            'remaining_seconds' => $remaining,
            'remaining_time' => $this->formatRemaining($remaining),
            'is_running' => $running,
            'can_add_items' => $running,
            'timezone' => $tz,
            'message' => $message,
        ];
        if (!$isWholesale) {
            $payload['minutes'] = $minutes;
            $payload['add_minutes'] = $minutes;
        }

        return $payload;
    }

    public function assertCanModify(Order $order): void
    {
        if ($this->canModify($order)) {
            return;
        }
        if ($this->isWholesale($order)) {
            throw new \RuntimeException($this->wholesale->lockedMessage());
        }
        throw new \RuntimeException('The time to add items to this order has ended.');
    }

    public function addSessionCartToOrder(Order $order, array $cart, array $meta = []): Order
    {
        $this->assertCanModify($order);

        return DB::transaction(function () use ($order, $cart, $meta) {
            $locked = Order::where('id', $order->id)->lockForUpdate()->first();
            $this->assertCanModify($locked);

            $previousTotal = (float) $locked->total_amount;

            foreach ($cart as $details) {
                if (empty($details['product_id'])) {
                    continue;
                }
                $qty = max(1, (int) ($details['quantity'] ?? 1));
                $price = floatval($details['price'] ?? 0);
                $compId = $details['complementary']['id'] ?? ($details['complementary_id'] ?? null);
                if (!$compId) {
                    $catalog = Product::with('complementaryProductSingle.complementary')->find($details['product_id']);
                    $compId = optional(optional($catalog)->complementaryProductSingle)->complementary->id ?? null;
                }
                $size = $this->normalizeLineSize($details['size'] ?? null);
                $toppingMap = is_array($details['toppings_by_category'] ?? null) ? $details['toppings_by_category'] : [];
                $toppingKey = $this->toppingFingerprintFromMap($toppingMap);

                $matches = OrderItem::where('order_id', $locked->id)
                    ->where('product_id', $details['product_id'])
                    ->lockForUpdate()
                    ->get()
                    ->filter(function ($row) use ($size, $toppingKey) {
                        return $this->normalizeLineSize($row->product_size) === $size
                            && $this->itemToppingFingerprint($row) === $toppingKey;
                    })->values();

                if ($matches->isNotEmpty()) {
                    $keep = $matches->first();
                    $extraQty = 0;
                    foreach ($matches->skip(1) as $dup) {
                        $extraQty += (int) $dup->quantity;
                        OrderItemToppings::where('order_item_id', $dup->id)->delete();
                        $dup->delete();
                    }
                    $keep->quantity = (int) $keep->quantity + $extraQty + $qty;
                    $keep->product_price = $price > 0 ? $price : $keep->product_price;
                    $keep->sub_total = floatval($keep->product_price) * (int) $keep->quantity;
                    if ($compId && !$keep->product_complementary_id) {
                        $keep->product_complementary_id = $compId;
                    }
                    $keep->save();
                    continue;
                }

                $orderItem = new OrderItem();
                $orderItem->order_id = $locked->id;
                $orderItem->product_id = $details['product_id'];
                $orderItem->product_complementary_id = $compId;
                $orderItem->product_size = $size;
                $orderItem->product_price = $price;
                $orderItem->branch_id = $details['branch_id'] ?? $locked->branch_id;
                $orderItem->product_name = $details['name'] ?? '';
                $orderItem->quantity = $qty;
                $orderItem->sub_total = $price * $qty;
                $orderItem->delivery_status = $details['delivery_status'] ?? null;
                $orderItem->delivery_address = $details['delivery_address'] ?? null;
                $orderItem->save();

                if (!empty($toppingMap)) {
                    foreach ($toppingMap as $categoryId => $toppingIds) {
                        foreach ((array) $toppingIds as $toppingId) {
                            $row = new OrderItemToppings();
                            $row->order_item_id = $orderItem->id;
                            $row->topping_id = $toppingId;
                            $row->category_id = is_numeric($categoryId) ? $categoryId : ($toppingId['category_id'] ?? null);
                            $row->save();
                        }
                    }
                }
            }

            $this->recalculateTotals($locked);
            $this->applyDriveInDiscount($locked, $locked->order_type);
            $locked->last_modified_at = $this->utcNow();
            $this->startAddWindow($locked);
            $locked->save();

            $this->supersedeReceipts($locked);
            try {
                $this->generateReceipt($locked, 'updated');
            } catch (\Throwable $e) {
                Log::warning('Receipt rotate failed after add-items', [
                    'order_id' => $locked->id,
                    'error' => $e->getMessage(),
                ]);
            }
            $this->rememberActiveOrder($locked);

            OrderModification::create([
                'order_id' => $locked->id,
                'user_id' => $locked->user_id,
                'action' => 'add_items',
                'payload' => $meta,
                'previous_total' => $previousTotal,
                'new_total' => $locked->total_amount,
                'receipt_version' => $locked->receipt_version,
            ]);

            return $locked->fresh(['orderItem']);
        });
    }

    public function removeItems(Order $order, array $itemIds): Order
    {
        $this->assertCanModify($order);
        $ids = array_values(array_unique(array_map('intval', $itemIds)));
        $ids = array_values(array_filter($ids, function ($id) {
            return $id > 0;
        }));
        if (!$ids) {
            throw new \RuntimeException('Send item_ids as an array of order line ids, e.g. {"order_id":665,"item_ids":[12]}. Use items[].id from GET /api/orders/665, not the product id.');
        }

        return DB::transaction(function () use ($order, $ids) {
            $locked = Order::where('id', $order->id)->lockForUpdate()->first();
            $this->assertCanModify($locked);
            $previousTotal = (float) $locked->total_amount;

            $items = OrderItem::where('order_id', $locked->id)->whereIn('id', $ids)->lockForUpdate()->get();
            if ($items->isEmpty()) {
                $items = OrderItem::where('order_id', $locked->id)->whereIn('product_id', $ids)->lockForUpdate()->get();
            }
            if ($items->isEmpty()) {
                $valid = OrderItem::where('order_id', $locked->id)->get(['id', 'product_id', 'product_name']);
                $hint = $valid->isEmpty()
                    ? 'This order has no items left.'
                    : 'Valid item_ids on this order: ' . $valid->pluck('id')->implode(', ') . '.';
                throw new \RuntimeException(
                    'Item not found on this order. item_ids must be the line id (items[].id), not product_id. ' . $hint
                );
            }

            foreach ($items as $item) {
                OrderItemToppings::where('order_item_id', $item->id)->delete();
                $item->delete();
            }

            $remaining = OrderItem::where('order_id', $locked->id)->count();
            $this->recalculateTotals($locked);
            $this->applyDriveInDiscount($locked, $locked->order_type);
            $locked->last_modified_at = $this->utcNow();

            if ($remaining === 0) {
                $locked->add_items_until = null;
                $locked->status = 'Cancelled';
                $locked->save();
                $this->supersedeReceipts($locked);
                Session::forget('active_add_order_id');
                Session::forget('adding_to_order_id');
                OrderModification::create([
                    'order_id' => $locked->id,
                    'user_id' => $locked->user_id,
                    'action' => 'empty_order',
                    'payload' => ['item_ids' => $ids],
                    'previous_total' => $previousTotal,
                    'new_total' => 0,
                    'receipt_version' => $locked->receipt_version,
                ]);
                return $locked->fresh(['orderItem']);
            }

            $this->startAddWindow($locked);
            $locked->save();
            $this->supersedeReceipts($locked);
            try {
                $this->generateReceipt($locked, 'updated');
            } catch (\Throwable $e) {
                Log::warning('Receipt rotate failed after remove-items', [
                    'order_id' => $locked->id,
                    'error' => $e->getMessage(),
                ]);
            }
            $this->rememberActiveOrder($locked);

            OrderModification::create([
                'order_id' => $locked->id,
                'user_id' => $locked->user_id,
                'action' => 'remove_items',
                'payload' => ['item_ids' => $ids],
                'previous_total' => $previousTotal,
                'new_total' => $locked->total_amount,
                'receipt_version' => $locked->receipt_version,
            ]);

            return $locked->fresh(['orderItem']);
        });
    }

    public function updateItemQuantity(Order $order, int $itemId, int $quantity): Order
    {
        return $this->updateItemOptions($order, $itemId, ['quantity' => $quantity]);
    }

    public function updateItemOptions(Order $order, int $itemId, array $options): Order
    {
        $this->assertCanModify($order);

        return DB::transaction(function () use ($order, $itemId, $options) {
            $locked = Order::where('id', $order->id)->lockForUpdate()->first();
            $this->assertCanModify($locked);
            $previousTotal = (float) $locked->total_amount;

            $item = OrderItem::where('order_id', $locked->id)->where('id', $itemId)->lockForUpdate()->first();
            if (!$item) {
                $item = OrderItem::where('order_id', $locked->id)->where('product_id', $itemId)->lockForUpdate()->first();
            }
            if (!$item) {
                $valid = OrderItem::where('order_id', $locked->id)->get(['id', 'product_id', 'product_name']);
                $hint = $valid->isEmpty()
                    ? 'This order has no items.'
                    : 'Valid item_id values: ' . $valid->map(function ($row) {
                        return $row->id . ' (' . $row->product_name . ', product_id ' . $row->product_id . ')';
                    })->implode(', ') . '.';
                $productName = DB::table('products')->where('id', $itemId)->value('name');
                $looksLikeProduct = $productName
                    ? ' Received item_id ' . $itemId . ' is products.id (' . $productName . '), not order_items.id.'
                    : ' Received item_id ' . $itemId . '.';
                throw new \RuntimeException(
                    'Order item not found.' . $looksLikeProduct
                    . ' Use items[].id from GET /api/orders/{order_id} (not products.id). ' . $hint
                );
            }

            $quantity = array_key_exists('quantity', $options)
                ? (int) $options['quantity']
                : (int) $item->quantity;

            if ($quantity <= 0) {
                OrderItemToppings::where('order_item_id', $item->id)->delete();
                $item->delete();
                $action = 'remove_item';
            } else {
                $product = Product::with('variants')->find($item->product_id);
                $this->applyVariantToItem($item, $product, $options);
                if (array_key_exists('toppings', $options) || array_key_exists('toppings_by_category', $options)) {
                    $this->replaceItemToppings($item, $options);
                }
                $item->quantity = $quantity;
                $item->save();
                $action = 'update_item';
            }

            if (array_key_exists('delivery_status', $options) && !$this->isWholesale($locked)) {
                $this->applyFulfillment($locked, $options);
            }

            $this->recalculateTotals($locked);
            $this->applyDriveInDiscount($locked, $locked->order_type);
            $locked->last_modified_at = $this->utcNow();
            $this->startAddWindow($locked);
            $locked->save();

            $this->supersedeReceipts($locked);
            try {
                $this->generateReceipt($locked, 'updated');
            } catch (\Throwable $e) {
                Log::warning('Receipt rotate failed after item update', [
                    'order_id' => $locked->id,
                    'error' => $e->getMessage(),
                ]);
            }
            $this->rememberActiveOrder($locked);

            OrderModification::create([
                'order_id' => $locked->id,
                'user_id' => $locked->user_id,
                'action' => $action,
                'payload' => [
                    'item_id' => $itemId,
                    'quantity' => $quantity,
                    'variant_id' => $options['variant_id'] ?? null,
                    'toppings' => $options['toppings'] ?? ($options['toppings_by_category'] ?? null),
                    'delivery_status' => $options['delivery_status'] ?? null,
                    'delivery_address' => $options['delivery_address'] ?? null,
                ],
                'previous_total' => $previousTotal,
                'new_total' => $locked->total_amount,
                'receipt_version' => $locked->receipt_version,
            ]);

            return $locked->fresh(['orderItem.orderToppings.toppings']);
        });
    }

    public function updateOrderFulfillment(Order $order, array $options): Order
    {
        $this->assertCanModify($order);

        return DB::transaction(function () use ($order, $options) {
            $locked = Order::where('id', $order->id)->lockForUpdate()->first();
            $this->assertCanModify($locked);
            $previousTotal = (float) $locked->total_amount;
            $this->applyFulfillment($locked, $options);
            $this->recalculateTotals($locked);
            $this->applyDriveInDiscount($locked, $locked->order_type);
            $locked->last_modified_at = $this->utcNow();
            $this->startAddWindow($locked);
            $locked->save();
            $this->supersedeReceipts($locked);
            try {
                $this->generateReceipt($locked, 'updated');
            } catch (\Throwable $e) {
                Log::warning('Receipt rotate failed after fulfillment update', [
                    'order_id' => $locked->id,
                    'error' => $e->getMessage(),
                ]);
            }
            $this->rememberActiveOrder($locked);
            OrderModification::create([
                'order_id' => $locked->id,
                'user_id' => $locked->user_id,
                'action' => 'update_fulfillment',
                'payload' => [
                    'delivery_status' => $options['delivery_status'] ?? null,
                    'delivery_address' => $options['delivery_address'] ?? null,
                ],
                'previous_total' => $previousTotal,
                'new_total' => $locked->total_amount,
                'receipt_version' => $locked->receipt_version,
            ]);

            return $locked->fresh(['orderItem']);
        });
    }

    protected function applyFulfillment(Order $order, array $options): void
    {
        if (!Schema::hasColumn('order_items', 'delivery_status')) {
            return;
        }

        $status = (int) ($options['delivery_status'] ?? 1);
        if (!in_array($status, [1, 2], true)) {
            $status = 1;
        }

        $address = trim((string) ($options['delivery_address'] ?? ''));
        $lat = $options['lat'] ?? null;
        $lng = $options['lng'] ?? null;

        if ($status === 2) {
            if ($address === '') {
                throw new \RuntimeException('Please enter a delivery address.');
            }
            $existing = OrderItem::where('order_id', $order->id)->first();
            $sameAddress = $existing && trim((string) ($existing->delivery_address ?? '')) === $address
                && (int) ($existing->delivery_status ?? 0) === 2;
            $hasCoords = $lat !== null && $lat !== '' && $lng !== null && $lng !== '';
            if (!$hasCoords && !$sameAddress) {
                throw new \RuntimeException('Please select a valid address from the suggestions.');
            }
            if (!$hasCoords) {
                $lat = null;
                $lng = null;
            }
        } else {
            $address = null;
        }

        $items = OrderItem::where('order_id', $order->id)->get();
        foreach ($items as $row) {
            $row->delivery_status = (string) $status;
            if (Schema::hasColumn('order_items', 'delivery_address')) {
                $row->delivery_address = $address;
            }
            $row->save();
        }

        if (Schema::hasColumn('orders', 'delivery_charge')) {
            if ($status === 2 && $lat && $lng) {
                $order->delivery_charge = $this->deliveryChargeForCoords($items->first(), $lat, $lng);
            } elseif ($status !== 2) {
                $order->delivery_charge = 0;
            }
        }
    }

    protected function deliveryChargeForCoords($item, $lat, $lng): float
    {
        $branch = $item && $item->branch_id
            ? Branch::find($item->branch_id)
            : Branch::where('status', 1)->first();
        $branchLat = optional($branch)->latitude ?? 0;
        $branchLng = optional($branch)->longitude ?? 0;
        $distance = $this->distanceMiles($branchLat, $branchLng, $lat, $lng);
        if ($distance <= 1) {
            return 1.99;
        }
        if ($distance <= 2) {
            return 2.99;
        }
        if ($distance <= 3) {
            return 3.49;
        }
        if ($distance <= 5) {
            return 4.99;
        }
        return 5.99;
    }

    protected function distanceMiles($originLat, $originLng, $destLat, $destLng): float
    {
        try {
            $apiKey = env('GOOGLE_MAPS_API_KEY');
            if (!$apiKey) {
                return 0;
            }
            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => $originLat . ',' . $originLng,
                'destinations' => $destLat . ',' . $destLng,
                'units' => 'imperial',
                'key' => $apiKey,
            ]);
            $data = $response->json();
            if (isset($data['rows'][0]['elements'][0]['distance']['value'])) {
                return ((float) $data['rows'][0]['elements'][0]['distance']['value']) * 0.000621371;
            }
        } catch (\Throwable $e) {
            Log::warning('Delivery distance lookup failed', ['error' => $e->getMessage()]);
        }
        return 0;
    }

    public function recalculateTotals(Order $order): void
    {
        $items = OrderItem::where('order_id', $order->id)->get();
        $subtotal = 0;
        foreach ($items as $item) {
            $line = floatval($item->product_price) * floatval($item->quantity);
            $toppings = OrderItemToppings::where('order_item_id', $item->id)->get();
            foreach ($toppings as $toppingRow) {
                $topping = Topping::find($toppingRow->topping_id);
                if ($topping) {
                    $line += floatval($topping->price) * floatval($item->quantity);
                }
            }
            $item->sub_total = $line;
            $item->save();
            $subtotal += $line;
        }

        $tax = 0;
        $first = $items->first();
        if ($first && $first->branch) {
            $tax = floatval($first->branch->tax ?? 0);
        }

        $delivery = floatval($order->delivery_charge ?? 0);
        $tip = floatval($items->sum('tip') ?: 0);
        $redeemed = floatval($order->redeemed ?? 0);
        $gateway = floatval($order->gateway_fee ?? 0);
        $discount = floatval($order->discount_amount ?? 0);

        $estimated = max(0, $subtotal + $tax + $delivery + $tip - $redeemed - $discount);
        $total = $estimated + $gateway;

        if (Schema::hasColumn('orders', 'subtotal')) {
            $order->subtotal = $subtotal;
        }
        if (Schema::hasColumn('orders', 'estimated_total')) {
            $order->estimated_total = $estimated;
        }
        $order->total_amount = $total;

        $paid = floatval($order->paid_amount ?? 0);
        if (Schema::hasColumn('orders', 'balance_due')) {
            $order->balance_due = max(0, round($total - $paid, 2));
        }
    }

    public function applyDriveInDiscount(Order $order, ?string $orderType = null): void
    {
        $type = strtolower((string) ($orderType ?: $order->order_type));
        if ($type !== 'drive_in' || !Schema::hasColumn('orders', 'discount_amount')) {
            return;
        }
        $percent = $this->driveInPercent();
        $items = OrderItem::where('order_id', $order->id)->get();
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += floatval($item->sub_total ?: ($item->product_price * $item->quantity));
        }
        $discount = round($subtotal * ($percent / 100), 2);
        $order->discount_amount = $discount;
        $order->discount_label = $percent . '% Drive-In off';
        $this->recalculateTotals($order);
    }

    public function generateReceipt(Order $order, string $reason = 'created'): OrderReceipt
    {
        $order->loadMissing([
            'orderItem.orderToppings.toppings',
            'orderItem.branch',
            'orderItem.product.complementaryProductSingle.complementary',
            'orderItem.complementaryProduct',
            'user',
        ]);
        $version = (int) ($order->receipt_number_next ?? (($order->receipt_version ?? 0) + ($reason === 'created' ? 0 : 1)));
        if ($reason === 'created') {
            $version = 1;
        } else {
            $version = ((int) ($order->receipt_version ?? 1)) + 1;
        }

        $receipt = OrderReceipt::create([
            'order_id' => $order->id,
            'version' => $version,
            'receipt_number' => $order->code . '-R' . $version,
            'status' => 'active',
            'snapshot' => $this->receiptSnapshot($order, $version),
        ]);

        if (Schema::hasColumn('orders', 'receipt_version')) {
            $order->receipt_version = $version;
            $order->save();
        }

        return $receipt;
    }

    public function supersedeReceipts(Order $order): void
    {
        OrderReceipt::where('order_id', $order->id)
            ->where('status', 'active')
            ->update([
                'status' => 'superseded',
                'superseded_at' => $this->time->now(),
            ]);
    }

    public function latestReceipt(Order $order): ?OrderReceipt
    {
        return OrderReceipt::where('order_id', $order->id)
            ->where('status', 'active')
            ->orderByDesc('version')
            ->first();
    }

    public function recordPayment(Order $order, float $amount, string $method, string $type = 'original', array $extra = []): void
    {
        if (Schema::hasTable('order_payments')) {
            OrderPayment::create([
                'order_id' => $order->id,
                'type' => $type,
                'method' => $method,
                'amount' => $amount,
                'status' => $extra['status'] ?? 'recorded',
                'stripe_session_id' => $extra['stripe_session_id'] ?? null,
                'notes' => $extra['notes'] ?? null,
            ]);
        }

        if (Schema::hasColumn('orders', 'paid_amount')) {
            $order->paid_amount = floatval($order->paid_amount) + $amount;
            if (Schema::hasColumn('orders', 'balance_due')) {
                $order->balance_due = max(0, floatval($order->total_amount) - floatval($order->paid_amount));
            }
            $order->save();
        }
    }

    public function activeModifiableOrder(?int $userId = null): ?Order
    {
        $now = $this->utcNow()->format('Y-m-d H:i:s');
        $sessionId = Session::get('active_add_order_id');

        if (!$userId) {
            if (!$sessionId) {
                return null;
            }
            $fromSession = Order::find($sessionId);
            return ($fromSession && $this->canModify($fromSession)) ? $fromSession : null;
        }

        $order = Order::query()
            ->where('user_id', $userId)
            ->whereNotIn('status', ['Delivered', 'Cancelled', 'Canceled', 'completed'])
            ->where(function ($q) {
                $q->whereNull('order_type')
                    ->orWhereNotIn('order_type', ['wholesale', 'dessert_wholesale']);
            })
            ->whereNotNull('add_items_until')
            ->where('add_items_until', '>', $now)
            ->latest()
            ->first();

        if ($order && $this->canModify($order)) {
            return $order;
        }

        if ($sessionId) {
            $fromSession = Order::find($sessionId);
            if ($fromSession && $this->canModify($fromSession) && (int) $fromSession->user_id === (int) $userId) {
                return $fromSession;
            }
        }

        return null;
    }

    protected function syncPaidAmount(Order $order): void
    {
        if (!Schema::hasColumn('orders', 'paid_amount')) {
            return;
        }
        $method = strtolower((string) $order->payment);
        if ($this->isOnlinePayment($method)) {
            $order->paid_amount = floatval($order->total_amount);
            $order->balance_due = 0;
        } else {
            $order->paid_amount = $order->paid_amount ?: 0;
            $order->balance_due = floatval($order->total_amount);
        }
    }

    protected function receiptSnapshot(Order $order, int $version): array
    {
        $first = $order->orderItem->first();
        $orderFulfillment = $first
            ? \App\Support\CartCheckout::fulfillmentDetails($first, $order)
            : null;
        $items = [];
        foreach ($order->orderItem as $item) {
            $modifiers = [];
            foreach ($item->orderToppings as $topping) {
                $modifiers[] = [
                    'name' => optional($topping->toppings)->name,
                    'price' => optional($topping->toppings)->price,
                ];
            }
            $ff = \App\Support\CartCheckout::fulfillmentDetails($item, $order);
            $gift = $item->complimentaryGift();
            $items[] = [
                'name' => $item->product_name,
                'size' => $item->displaySize(),
                'qty' => $item->quantity,
                'unit_price' => $item->product_price,
                'line_total' => $item->lineTotal(),
                'modifiers' => $modifiers,
                'fulfillment' => $ff['label'],
                'fulfillment_details' => $ff,
                'address' => $ff['display_address'],
                'delivery_status' => $item->delivery_status,
                'complementary' => $gift ? [
                    'id' => $gift->id,
                    'name' => $gift->name,
                    'image' => \App\Support\StorefrontApiPresenter::imageUrl($gift->image ?? null),
                    'badge' => 'BUY 1 GET 1 FREE',
                ] : null,
            ];
        }

        return [
            'brand' => \App\Support\CartCheckout::companyName(),
            'logo_url' => \App\Support\CartCheckout::logoUrl(),
            'company' => [
                'name' => \App\Support\CartCheckout::companyName(),
                'logo_url' => \App\Support\CartCheckout::logoUrl(),
                'branch_name' => $orderFulfillment['pickup_name'] ?? optional(optional($first)->branch)->name,
                'address' => $orderFulfillment['pickup_address'] ?? optional(optional($first)->branch)->location,
            ],
            'order_number' => $order->code,
            'receipt_number' => $order->code . '-R' . $version,
            'version' => $version,
            'date' => optional($order->created_at)->format('Y-m-d'),
            'time' => optional($order->created_at)->format('H:i'),
            'customer' => optional($order->user)->name,
            'order_type' => $order->order_type,
            'status' => $order->status,
            'payment' => $this->receiptPaymentMethod($order->payment),
            'payment_status' => $this->receiptPaymentStatus($order),
            'fulfillment' => $orderFulfillment,
            'items' => $items,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount_amount,
            'discount_label' => $order->discount_label,
            'delivery_charge' => $order->delivery_charge,
            'tax' => optional(optional($first)->branch)->tax ?? 0,
            'grand_total' => $order->total_amount,
            'wholesale_delivery_date' => $order->wholesale_delivery_date,
            'scheduled_at' => $order->scheduled_at,
            'notes' => $order->notes,
            'thank_you' => 'Thank you',
        ];
    }

    public function isOnlinePayment($payment): bool
    {
        $method = strtolower(trim((string) $payment));
        return in_array($method, [
            'stripe',
            'card',
            'online',
            'paid',
            'test_skip_stripe',
        ], true);
    }

    public function receiptPaymentMethod($payment): string
    {
        return 'Online';
    }

    public function receiptPaymentStatus(Order $order): string
    {
        return 'Paid';
    }

    public function paymentPayload(Order $order): array
    {
        $method = $this->receiptPaymentMethod($order->payment);
        $status = $this->receiptPaymentStatus($order);

        return [
            'method' => $method,
            'method_key' => 'online',
            'status' => $status,
            'label' => $method . ' / ' . $status,
            'paid_amount' => round((float) ($order->paid_amount ?? $order->total_amount ?? 0), 2),
            'balance_due' => 0,
        ];
    }

    protected function formatRemaining(int $seconds): string
    {
        if ($seconds >= 3600) {
            $h = (int) floor($seconds / 3600);
            $m = (int) floor(($seconds % 3600) / 60);
            return $h . 'h ' . str_pad((string) $m, 2, '0', STR_PAD_LEFT) . 'm';
        }
        $m = (int) floor($seconds / 60);
        $s = $seconds % 60;
        return str_pad((string) $m, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) $s, 2, '0', STR_PAD_LEFT);
    }

    protected function applyVariantToItem(OrderItem $item, ?Product $product, array $options): void
    {
        $variants = collect();
        if ($product) {
            $variants = $product->relationLoaded('variants') ? $product->variants : $product->variants()->get();
        }

        $variant = null;
        if (!empty($options['variant_id'])) {
            $variant = DB::table('product_variants')->where('id', (int) $options['variant_id'])->first();
            if (!$variant && $product) {
                $variants = $product->relationLoaded('variants') ? $product->variants : $product->variants()->get();
                $variant = $variants->firstWhere('id', (int) $options['variant_id']);
            }
            if (!$variant) {
                throw new \RuntimeException('That size is not available for this item.');
            }
        } else {
            $size = strtolower(trim((string) ($options['product_size'] ?? $item->product_size ?? '')));
            if ($size !== '' && Schema::hasTable('product_variants')) {
                $ids = \App\Support\ProductEditOptions::relatedProductIds($item->product_id, $item->product_name);
                $variant = DB::table('product_variants')
                    ->whereIn('product_id', $ids)
                    ->get()
                    ->first(function ($row) use ($size) {
                        return strtolower(trim((string) $row->size)) === $size;
                    });
            }
        }

        if (!$variant && (isset($options['toppings']) || isset($options['toppings_by_category']))) {
            $variant = $variants->first(function ($row) {
                return strtolower((string) $row->size) === 'regular' && (float) $row->price > 0;
            }) ?: $variants->first();
        }

        if ($variant) {
            $item->product_size = $variant->size;
            $price = (float) $variant->price;
            if ($price <= 0) {
                $price = (float) ($variant->original_price ?? 0);
            }
            $item->product_price = $price;
            if (Schema::hasColumn('order_items', 'variant_id')) {
                $item->variant_id = $variant->id;
            }
        }
    }

    protected function replaceItemToppings(OrderItem $item, array $options): void
    {
        $rows = [];
        if (!empty($options['toppings_by_category']) && is_array($options['toppings_by_category'])) {
            foreach ($options['toppings_by_category'] as $categoryId => $toppingIds) {
                foreach ((array) $toppingIds as $toppingId) {
                    if (!$toppingId) {
                        continue;
                    }
                    $rows[] = [
                        'topping_id' => (int) $toppingId,
                        'category_id' => is_numeric($categoryId) ? (int) $categoryId : null,
                    ];
                }
            }
        } else {
            foreach ((array) ($options['toppings'] ?? []) as $entry) {
                if (is_array($entry)) {
                    $toppingId = (int) ($entry['topping_id'] ?? $entry['id'] ?? 0);
                    $categoryId = isset($entry['category_id']) ? (int) $entry['category_id'] : null;
                } else {
                    $toppingId = (int) $entry;
                    $categoryId = isset($options['topping_category'][$toppingId])
                        ? (int) $options['topping_category'][$toppingId]
                        : null;
                }
                if ($toppingId > 0) {
                    $rows[] = ['topping_id' => $toppingId, 'category_id' => $categoryId];
                }
            }
        }

        OrderItemToppings::where('order_item_id', $item->id)->delete();
        foreach ($rows as $row) {
            $topping = Topping::find($row['topping_id']);
            if (!$topping) {
                continue;
            }
            $line = new OrderItemToppings();
            $line->order_item_id = $item->id;
            $line->topping_id = $row['topping_id'];
            $line->category_id = $row['category_id'];
            if (Schema::hasColumn('order_item_toppings', 'topping_price')) {
                $line->topping_price = $topping->price;
            }
            $line->save();
        }
    }

    protected function deadlineUtc(): Carbon
    {
        return Carbon::now('UTC')->addMinutes($this->addToOrderMinutes());
    }

    protected function utcNow(): Carbon
    {
        return Carbon::now('UTC');
    }

    protected function asCarbonUtc($value): Carbon
    {
        if ($value instanceof Carbon) {
            return Carbon::parse($value->format('Y-m-d H:i:s'), 'UTC');
        }
        return Carbon::parse((string) $value, 'UTC');
    }

    protected function asCarbon($value): Carbon
    {
        return $this->asCarbonUtc($value);
    }

    /**
     * Items saved after a non-AUTO_INCREMENT insertGetId() were stored as order_id=0.
     * Attach those rows to the user's order created just before the item.
     */
    public function reattachOrphanItemsForUser(int $userId): void
    {
        $orders = Order::where('user_id', $userId)->orderBy('created_at')->get();
        if ($orders->isEmpty()) {
            return;
        }

        $orphans = OrderItem::where(function ($q) {
            $q->where('order_id', 0)->orWhereNull('order_id');
        })->orderBy('id')->get();

        foreach ($orphans as $item) {
            if (!$item->created_at) {
                continue;
            }
            $match = $orders
                ->filter(function ($order) use ($item) {
                    return $order->created_at
                        && $order->created_at->lte($item->created_at)
                        && $order->created_at->diffInSeconds($item->created_at) <= 180;
                })
                ->sortByDesc(function ($order) {
                    return $order->created_at->timestamp;
                })
                ->first();

            if ($match) {
                $item->order_id = $match->id;
                $item->save();
            }
        }

        $toppingOrphans = OrderItemToppings::where(function ($q) {
            $q->where('order_item_id', 0)->orWhereNull('order_item_id');
        })->get();

        foreach ($toppingOrphans as $row) {
            if (!$row->created_at) {
                continue;
            }
            $item = OrderItem::where('created_at', '>=', $row->created_at->copy()->subSeconds(20))
                ->where('created_at', '<=', $row->created_at->copy()->addSeconds(5))
                ->where('order_id', '>', 0)
                ->orderByDesc('id')
                ->first();
            if ($item) {
                $row->order_item_id = $item->id;
                $row->save();
            }
        }
    }

    protected function normalizeLineSize($size): ?string
    {
        if ($size === null) {
            return null;
        }
        $size = trim((string) $size);
        if ($size === '' || strcasecmp($size, 'null') === 0 || strcasecmp($size, 'default') === 0) {
            return null;
        }
        return strtolower($size);
    }

    protected function toppingFingerprintFromMap(array $toppingMap): string
    {
        $ids = [];
        foreach ($toppingMap as $toppingIds) {
            foreach ((array) $toppingIds as $toppingId) {
                if (is_array($toppingId)) {
                    $toppingId = $toppingId['topping_id'] ?? null;
                }
                $id = (int) $toppingId;
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }
        $ids = array_values(array_unique($ids));
        sort($ids);
        return implode(',', $ids);
    }

    protected function itemToppingFingerprint(OrderItem $item): string
    {
        $ids = OrderItemToppings::where('order_item_id', $item->id)->pluck('topping_id')->map(function ($id) {
            return (int) $id;
        })->filter()->unique()->sort()->values()->all();
        return implode(',', $ids);
    }
}
