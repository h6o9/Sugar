<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemToppings;
use App\Models\OrderModification;
use App\Models\OrderPayment;
use App\Models\OrderReceipt;
use App\Models\Topping;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class OrderLifecycleService
{
    /** @var BusinessTimeService */
    protected $time;

    public function __construct(BusinessTimeService $time)
    {
        $this->time = $time;
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
        $type = is_object($order) ? ($order->order_type ?? $order->menu_type ?? '') : (string) $order;
        return in_array(strtolower((string) $type), ['wholesale', 'dessert_wholesale'], true);
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
        // #region agent log
        file_put_contents(base_path('debug-1796d5.log'), json_encode([
            'sessionId' => '1796d5',
            'hypothesisId' => 'W3',
            'location' => 'OrderLifecycleService.php:initializeNewOrder',
            'message' => 'init order type and timer window',
            'data' => [
                'order_id' => $order->id,
                'orderType' => $orderType,
                'menuType' => $menuType,
                'isWholesale' => $isWholesale,
                'has_wholesale_date' => !empty($meta['wholesale_delivery_date']),
            ],
            'timestamp' => (int) round(microtime(true) * 1000),
        ]) . "\n", FILE_APPEND);
        // #endregion
        if (!$isWholesale && empty($meta['is_scheduled']) && !$this->time->isOpen()) {
            $meta['is_scheduled'] = true;
            $meta['scheduled_at'] = $this->time->nextOpening();
        }

        $this->startAddWindow($order, $isWholesale);

        if (!empty($meta['is_scheduled']) && Schema::hasColumn('orders', 'is_scheduled')) {
            $order->is_scheduled = true;
            $order->scheduled_at = $meta['scheduled_at'] ?? null;
            if ($order->scheduled_at) {
                $order->status = 'Scheduled';
            }
        }

        if (!empty($meta['wholesale_delivery_date']) && Schema::hasColumn('orders', 'wholesale_delivery_date')) {
            $order->wholesale_delivery_date = $meta['wholesale_delivery_date'];
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
        $order->add_items_until = $wholesale ? null : $this->deadlineUtc();
    }

    public function rememberActiveOrder(Order $order): void
    {
        if (!$order->id || !$order->add_items_until) {
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

    public function modifyDeadline(Order $order): ?Carbon
    {
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

    public function canModify(Order $order): bool
    {
        if ($this->isWholesale($order)) {
            return false;
        }
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
        return [
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
            'add_minutes' => $this->addToOrderMinutes(),
            'receipt_version' => $order->receipt_version ?? 1,
            'is_scheduled' => (bool) ($order->is_scheduled ?? false),
            'scheduled_time' => $order->scheduled_at,
            'order_type' => $order->order_type,
            'wholesale_delivery_date' => $order->wholesale_delivery_date,
            'message' => $canAdd
                ? 'You have ' . $this->addToOrderMinutes() . ' minutes to add or remove items. Any change cancels the old receipt, issues a new one, and restarts this timer. If you do nothing, the order stays placed.'
                : ($this->isWholesale($order) ? null : 'Your time to change this order has ended. The order stays as placed.'),
        ];
    }

    public function assertCanModify(Order $order): void
    {
        if ($this->isWholesale($order)) {
            throw new \RuntimeException('Wholesale orders cannot be modified after checkout.');
        }
        if (!$this->canModify($order)) {
            throw new \RuntimeException('The time to add items to this order has ended.');
        }
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
                $orderItem = new OrderItem();
                $orderItem->order_id = $locked->id;
                $orderItem->product_id = $details['product_id'];
                $orderItem->product_complementary_id = $details['complementary']['id'] ?? ($details['complementary_id'] ?? null);
                $orderItem->product_size = $details['size'] ?? 'NULL';
                $orderItem->product_price = $details['price'];
                $orderItem->branch_id = $details['branch_id'] ?? $locked->branch_id;
                $orderItem->product_name = $details['name'] ?? '';
                $orderItem->quantity = $details['quantity'] ?? 1;
                $orderItem->sub_total = floatval($details['price']) * floatval($details['quantity'] ?? 1);
                $orderItem->delivery_status = $details['delivery_status'] ?? null;
                $orderItem->delivery_address = $details['delivery_address'] ?? null;
                $orderItem->save();

                if (!empty($details['toppings_by_category']) && is_array($details['toppings_by_category'])) {
                    foreach ($details['toppings_by_category'] as $categoryId => $toppingIds) {
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
        if (!$ids) {
            throw new \RuntimeException('Please select the item you want to remove.');
        }

        return DB::transaction(function () use ($order, $ids) {
            $locked = Order::where('id', $order->id)->lockForUpdate()->first();
            $this->assertCanModify($locked);
            $previousTotal = (float) $locked->total_amount;

            $items = OrderItem::where('order_id', $locked->id)->whereIn('id', $ids)->lockForUpdate()->get();
            if ($items->isEmpty()) {
                throw new \RuntimeException('Please select the item you want to remove.');
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
        $this->assertCanModify($order);

        return DB::transaction(function () use ($order, $itemId, $quantity) {
            $locked = Order::where('id', $order->id)->lockForUpdate()->first();
            $this->assertCanModify($locked);
            $previousTotal = (float) $locked->total_amount;

            $item = OrderItem::where('order_id', $locked->id)->where('id', $itemId)->lockForUpdate()->first();
            if (!$item) {
                throw new \RuntimeException('Order item not found.');
            }

            if ($quantity <= 0) {
                OrderItemToppings::where('order_item_id', $item->id)->delete();
                $item->delete();
                $action = 'remove_item';
            } else {
                $item->quantity = $quantity;
                $item->sub_total = floatval($item->product_price) * $quantity;
                $item->save();
                $action = 'update_quantity';
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
                'payload' => ['item_id' => $itemId, 'quantity' => $quantity],
                'previous_total' => $previousTotal,
                'new_total' => $locked->total_amount,
                'receipt_version' => $locked->receipt_version,
            ]);

            return $locked->fresh(['orderItem']);
        });
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
        $order->loadMissing(['orderItem.orderToppings.toppings', 'orderItem.branch', 'user']);
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
        if (in_array($method, ['stripe', 'card', 'online'], true)) {
            $order->paid_amount = floatval($order->total_amount);
            $order->balance_due = 0;
        } else {
            $order->paid_amount = $order->paid_amount ?: 0;
            $order->balance_due = floatval($order->total_amount);
        }
    }

    protected function receiptSnapshot(Order $order, int $version): array
    {
        $items = [];
        foreach ($order->orderItem as $item) {
            $modifiers = [];
            foreach ($item->orderToppings as $topping) {
                $modifiers[] = [
                    'name' => optional($topping->toppings)->name,
                    'price' => optional($topping->toppings)->price,
                ];
            }
            $items[] = [
                'name' => $item->product_name,
                'size' => $item->product_size,
                'qty' => $item->quantity,
                'unit_price' => $item->product_price,
                'line_total' => $item->sub_total,
                'modifiers' => $modifiers,
                'fulfillment' => \App\Support\CartCheckout::fulfillmentLabel($item, $order->order_type),
                'delivery_status' => $item->delivery_status,
            ];
        }

        return [
            'brand' => 'Sugar Pappi',
            'order_number' => $order->code,
            'receipt_number' => $order->code . '-R' . $version,
            'version' => $version,
            'date' => optional($order->created_at)->format('Y-m-d'),
            'time' => optional($order->created_at)->format('H:i'),
            'customer' => optional($order->user)->name,
            'order_type' => $order->order_type,
            'status' => $order->status,
            'payment' => $order->payment,
            'payment_status' => floatval($order->balance_due ?? 0) > 0 ? 'Balance due' : 'Paid',
            'items' => $items,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount_amount,
            'discount_label' => $order->discount_label,
            'delivery_charge' => $order->delivery_charge,
            'tax' => optional(optional($order->orderItem->first())->branch)->tax ?? 0,
            'grand_total' => $order->total_amount,
            'wholesale_delivery_date' => $order->wholesale_delivery_date,
            'scheduled_at' => $order->scheduled_at,
            'notes' => $order->notes,
        ];
    }

    protected function formatRemaining(int $seconds): string
    {
        $m = floor($seconds / 60);
        $s = $seconds % 60;
        return str_pad((string) $m, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) $s, 2, '0', STR_PAD_LEFT);
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
}
