<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BusinessSetting;
use App\Models\CiboExpress;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReceipt;
use App\Models\Product;
use App\Services\BusinessTimeService;
use App\Services\OrderLifecycleService;
use App\Services\WholesaleScheduleService;
use App\Support\MenuCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class StorefrontController extends Controller
{
    /** @var BusinessTimeService */
    protected $time;
    /** @var WholesaleScheduleService */
    protected $wholesale;
    /** @var OrderLifecycleService */
    protected $orders;

    public function __construct(
        BusinessTimeService $time,
        WholesaleScheduleService $wholesale,
        OrderLifecycleService $orders
    ) {
        $this->time = $time;
        $this->wholesale = $wholesale;
        $this->orders = $orders;
    }

    public function businessStatus()
    {
        return response()->json($this->time->status());
    }

    public function selectStore(Request $request)
    {
        $branch = Branch::findOrFail($request->branch_id);
        if (!(int) ($branch->is_orderable ?? 0) && stripos($branch->name . ' ' . $branch->location . ' ' . ($branch->city_label ?? ''), 'Manchester') === false) {
            return response()->json(['status' => false, 'message' => 'This store is not currently available for ordering.'], 422);
        }
        Session::put('selected_branch_id', $branch->id);
        Session::put('selected_order_type', $request->input('order_type', 'standard'));
        $redirect = route('get-our-menu');
        // #region agent log
        file_put_contents(base_path('debug-1796d5.log'), json_encode([
            'sessionId' => '1796d5',
            'hypothesisId' => 'S2',
            'location' => 'StorefrontController.php:selectStore',
            'message' => 'store selected redirect',
            'data' => ['branch_id' => (int) $branch->id, 'redirect' => $redirect],
            'timestamp' => (int) round(microtime(true) * 1000),
        ]) . "\n", FILE_APPEND);
        // #endregion
        return response()->json(['status' => true, 'redirect' => $redirect]);
    }

    public function driveIn()
    {
        Session::put('selected_order_type', 'drive_in');
        try {
            $this->orders->cancelWholesaleAddToOrderSession();
        } catch (\Throwable $e) {
        }
        $products = $this->foodProducts();
        $hours = $this->time->status();
        return view('home.drive-in', compact('products', 'hours'));
    }

    public function pappiSpecial()
    {
        $menuCategories = MenuCatalog::forSpecial();
        $branches = Branch::all();
        $userId = Auth::guard('user')->id();
        $userTimeSlots = \App\Models\UserTimeSlotes::where('user_id', $userId)->first();
        $timeSlots = \App\Models\TimeSlot::all();
        $searchTerm = '';
        $filteredProducts = collect();
        $addingToOrder = false;
        try {
            $this->orders->cancelWholesaleAddToOrderSession();
            $addingToOrder = $this->orders->hasActiveAddToOrderSession($userId ? (int) $userId : null);
        } catch (\Throwable $e) {
            $addingToOrder = false;
        }
        $specialPage = true;
        $wholesaleMode = false;
        if (!$addingToOrder) {
            Session::put('selected_order_type', 'special');
        }
        $hours = $this->time->status();
        $defaultBranch = Branch::where('status', 1)->first() ?: Branch::first();

        return view('home.our-menu', compact(
            'menuCategories',
            'branches',
            'timeSlots',
            'userTimeSlots',
            'searchTerm',
            'filteredProducts',
            'addingToOrder',
            'specialPage',
            'wholesaleMode',
            'hours',
            'defaultBranch'
        ));
    }

    public function wholesale()
    {
        $menu = $this->findWholesaleMenu();
        $menuCategories = MenuCatalog::forWholesale();
        $dates = $this->wholesale->availableDates();
        $content = Schema::hasColumn('cibo_express', 'page_key')
            ? CiboExpress::where('page_key', 'wholesale')->first()
            : null;
        $hours = $this->time->status();
        $defaultBranch = Branch::where('status', 1)->first() ?: Branch::first();
        $branches = Branch::all();
        $wholesaleMode = true;
        $userId = Auth::guard('user')->id();
        $pendingAddId = Session::get('adding_to_order_id');
        $updatingOrder = null;
        if ($pendingAddId && $userId) {
            $updatingOrder = Order::where('id', $pendingAddId)->where('user_id', $userId)->first();
            if ($updatingOrder && $this->orders->isWholesale($updatingOrder)) {
                $this->restoreWholesaleSessionFromOrder($updatingOrder);
            }
        }
        $addingToOrder = false;
        try {
            $addingToOrder = $this->orders->hasActiveAddToOrderSession($userId ? (int) $userId : null);
        } catch (\Throwable $e) {
            $addingToOrder = false;
        }
        if (!$addingToOrder) {
            $updatingOrder = null;
        }
        $searchTerm = '';
        $filteredProducts = collect();
        // #region agent log
        file_put_contents(base_path('debug-1796d5.log'), json_encode([
            'sessionId' => '1796d5',
            'runId' => 'post-fix',
            'hypothesisId' => 'H6',
            'location' => 'StorefrontController.php:wholesale',
            'message' => 'wholesale page menus',
            'data' => [
                'wholesale_menu_id' => $menu->id ?? null,
                'wholesaleMode' => true,
                'has_date' => (bool) session('wholesale_delivery_date'),
                'date' => session('wholesale_delivery_date'),
                'date_count' => count($dates),
                'categories' => $menuCategories->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'name' => $m->name,
                        'type' => $m->type ?? null,
                        'product_count' => $m->product ? $m->product->count() : 0,
                    ];
                })->values(),
            ],
            'timestamp' => (int) round(microtime(true) * 1000),
        ]) . "\n", FILE_APPEND);
        // #endregion
        return view('home.our-menu', compact(
            'menuCategories',
            'dates',
            'content',
            'hours',
            'menu',
            'defaultBranch',
            'branches',
            'wholesaleMode',
            'addingToOrder',
            'updatingOrder',
            'searchTerm',
            'filteredProducts'
        ));
    }

    public function privateBookings()
    {
        $content = Schema::hasColumn('cibo_express', 'page_key')
            ? CiboExpress::where('page_key', 'private_booking')->first()
            : null;
        $whatsapp = preg_replace('/\D+/', '', BusinessSetting::getValue('whatsapp_number', '447727412922'));
        return view('home.private-bookings', compact('content', 'whatsapp'));
    }

    public function startAddToOrder(Request $request, $orderId)
    {
        try {
            $order = Order::where('id', $orderId)->where('user_id', Auth::guard('user')->id())->firstOrFail();
            $this->orders->assertCanModify($order);
            Session::put('adding_to_order_id', $order->id);
            Session::forget('cart');
            if ($this->orders->isWholesale($order)) {
                $this->restoreWholesaleSessionFromOrder($order);
                return redirect()->route('dessert-wholesale');
            }
            return redirect()->route('get-our-menu');
        } catch (\RuntimeException $e) {
            return redirect()->route('my-order')->with(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function confirmAddToOrder(Request $request)
    {
        $user = Auth::guard('user')->user();
        $orderId = Session::get('adding_to_order_id');
        if (!$orderId) {
            return redirect()->route('my-cart')->with(['status' => false, 'message' => 'No active order to add items to.']);
        }
        $order = Order::where('id', $orderId)->where('user_id', $user->id)->first();
        if (!$order || !$this->orders->canModify($order)) {
            Session::forget('adding_to_order_id');
            $message = ($order && $this->orders->isWholesale($order))
                ? 'You can no longer update this order.'
                : 'Place this as a new order.';
            return redirect()->route('my-order')->with(['status' => false, 'message' => $message]);
        }
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('my-cart')->with(['status' => false, 'message' => 'Please add items before confirming.']);
        }

        try {
            $updated = $this->orders->addSessionCartToOrder($order, $cart, ['source' => 'web']);
        } catch (\Throwable $e) {
            Session::forget('adding_to_order_id');
            return redirect()->route('checkout')->with(['status' => false, 'message' => 'Could not add to the previous order. Place this as a new order.']);
        }
        $balance = floatval($updated->balance_due ?? 0);
        $payment = strtolower((string) $updated->payment);

        Session::forget('cart');
        Session::forget('adding_to_order_id');

        if (in_array($payment, ['stripe', 'card', 'online'], true) && $balance >= 0.30) {
            Session::put('pending_order_balance_id', $updated->id);
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'gbp',
                        'product_data' => ['name' => 'Additional items for order #' . $updated->code],
                        'unit_amount' => (int) round($balance * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('order.balance.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('my-order'),
            ]);
            return redirect($session->url);
        }

        $timerNote = $this->orders->isWholesale($updated)
            ? 'You can still add or remove items until 6 hours before wholesale delivery.'
            : $this->orders->addToOrderMinutes() . '-minute timer restarted.';
        return redirect()->route('my-order')->with(['status' => true, 'message' => 'Order #' . $updated->code . ' updated. Old receipt cancelled, new receipt issued. ' . $timerNote]);
    }

    public function balanceSuccess(Request $request)
    {
        $order = Order::where('id', Session::get('pending_order_balance_id'))
            ->where('user_id', Auth::guard('user')->id())
            ->firstOrFail();
        $sessionId = $request->get('session_id');
        if ($sessionId) {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            if (($session->payment_status ?? '') === 'paid') {
                $this->orders->recordPayment($order, floatval($order->balance_due), 'stripe', 'incremental', [
                    'stripe_session_id' => $sessionId,
                    'status' => 'paid',
                ]);
            }
        }
        Session::forget('pending_order_balance_id');
        return redirect()->route('my-order')->with(['status' => true, 'message' => 'Additional payment recorded for order #' . $order->code]);
    }

    public function editOrderItem($orderId, $itemId)
    {
        $order = Order::where('id', $orderId)->where('user_id', Auth::guard('user')->id())->firstOrFail();
        try {
            $this->orders->assertCanModify($order);
        } catch (\RuntimeException $e) {
            return redirect()->route('my-order')->with(['status' => false, 'message' => $e->getMessage()]);
        }

        $item = OrderItem::with(['orderToppings.toppings', 'branch'])
            ->where('order_id', $order->id)
            ->where('id', $itemId)
            ->firstOrFail();

        $options = \App\Support\ProductEditOptions::forItem($item);
        $product = $options['product'];
        $variants = $options['variants'];
        $toppingGroups = $options['topping_groups'];
        $selectedToppingIds = $options['selected_topping_ids'];

        return view('home.edit-order-item', compact(
            'order',
            'item',
            'product',
            'variants',
            'toppingGroups',
            'selectedToppingIds'
        ));
    }

    public function updateOrderItem(Request $request, $orderId, $itemId)
    {
        try {
            $order = Order::where('id', $orderId)->where('user_id', Auth::guard('user')->id())->firstOrFail();
            $qty = $request->has('quantity') ? (int) $request->input('quantity') : null;
            $options = [];
            if ($qty !== null) {
                $options['quantity'] = $qty;
            }
            if ($request->filled('variant_id')) {
                $options['variant_id'] = (int) $request->input('variant_id');
            }
            if ($request->boolean('update_toppings') || $request->has('toppings') || $request->has('toppings_by_category')) {
                $options['toppings_by_category'] = $request->input('toppings_by_category', []);
                $options['toppings'] = $request->input('toppings', []);
                $options['topping_category'] = $request->input('topping_category', []);
            }
            if ($request->filled('delivery_status') && !$this->orders->isWholesale($order)) {
                $options['delivery_status'] = (int) $request->input('delivery_status');
                $options['delivery_address'] = $request->input('delivery_address');
                $options['lat'] = $request->input('lat');
                $options['lng'] = $request->input('lng');
            }
            $this->orders->updateItemOptions($order, (int) $itemId, $options);
            $message = $this->orders->isWholesale($order)
                ? 'Item updated. Old receipt cancelled, new receipt issued.'
                : 'Item updated. Old receipt cancelled, new receipt issued, and the timer has started again.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return redirect()->route('my-order')->with(['status' => true, 'message' => $message]);
        } catch (\RuntimeException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function removeOrderItems(Request $request, $orderId)
    {
        try {
            $order = Order::where('id', $orderId)->where('user_id', Auth::guard('user')->id())->firstOrFail();
            $ids = $request->input('item_ids', []);
            if (!is_array($ids)) {
                $ids = [$ids];
            }
            $updated = $this->orders->removeItems($order, $ids);
        } catch (\RuntimeException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with(['status' => false, 'message' => $e->getMessage()]);
        }
        $remaining = $updated->orderItem ? $updated->orderItem->count() : 0;
        if ($remaining === 0) {
            $message = 'Item removed. Your order is now empty.';
        } elseif ($this->orders->isWholesale($updated)) {
            $message = 'Product removed. You can still update this order until 6 hours before wholesale delivery.';
        } else {
            $message = 'Product removed. The ' . $this->orders->addToOrderMinutes() . '-minute timer has started again.';
        }
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message, 'remaining' => $remaining]);
        }
        return redirect()->back()->with(['status' => true, 'message' => $message]);
    }

    public function editFulfillment($orderId)
    {
        $order = Order::with(['orderItem.branch'])->where('id', $orderId)->where('user_id', Auth::guard('user')->id())->firstOrFail();
        try {
            $this->orders->assertCanModify($order);
        } catch (\RuntimeException $e) {
            return redirect()->route('my-order')->with(['status' => false, 'message' => $e->getMessage()]);
        }
        $item = $order->orderItem->first();
        $pickupBranch = optional($item)->branch ?: Branch::where('status', 1)->first();
        $currentDelivery = (int) optional($item)->delivery_status === 2 ? 2 : 1;

        return view('home.edit-order-fulfillment', compact('order', 'item', 'pickupBranch', 'currentDelivery'));
    }

    public function updateFulfillment(Request $request, $orderId)
    {
        try {
            $order = Order::where('id', $orderId)->where('user_id', Auth::guard('user')->id())->firstOrFail();
            $this->orders->updateOrderFulfillment($order, [
                'delivery_status' => (int) $request->input('delivery_status', 1),
                'delivery_address' => $request->input('delivery_address'),
                'lat' => $request->input('lat'),
                'lng' => $request->input('lng'),
            ]);
            return redirect()->route('my-order')->with([
                'status' => true,
                'message' => 'Delivery method updated. Old receipt cancelled, new receipt issued.',
            ]);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with(['status' => false, 'message' => $e->getMessage()])->withInput();
        }
    }

    public function printReceipt($orderId)
    {
        $order = Order::with(['orderItem.orderToppings.toppings', 'orderItem.branch', 'user'])->findOrFail($orderId);
        $user = Auth::guard('user')->user();
        $admin = Auth::guard('admin')->user();
        if (!$admin && (!$user || $user->id != $order->user_id)) {
            abort(403);
        }
        $receipt = $this->orders->latestReceipt($order);
        if (!$receipt) {
            $receipt = $this->orders->generateReceipt($order, 'created');
        }
        return view('home.receipt-80mm', compact('order', 'receipt'));
    }

    public function setWholesaleDate(Request $request)
    {
        if (Session::get('adding_to_order_id')) {
            return redirect()->route('dessert-wholesale')->with([
                'status' => true,
                'message' => 'This delivery date is already set on your order. Add items to update it.',
            ]);
        }
        $date = $request->input('wholesale_delivery_date');
        if (!$this->wholesale->isValidDate($date)) {
            return redirect()->back()->with(['status' => false, 'message' => $this->wholesale->cutoffMessage()]);
        }
        Session::put('wholesale_delivery_date', $date);
        Session::put('selected_order_type', 'wholesale');
        return redirect()->route('dessert-wholesale')->with([
            'status' => true,
            'message' => 'Wholesale delivery saved: ' . $date . ' (7:00 PM – 10:00 PM). Now add items to cart, then Place Order from My Cart.',
        ]);
    }

    public function setSchedule(Request $request)
    {
        $status = $this->time->status();
        Session::put('scheduled_order', 1);
        Session::put('scheduled_at', $status['next_opening_at']);
        return redirect()->back()->with(['status' => true, 'message' => 'Your order will be scheduled for ' . $status['next_opening_display']]);
    }

    protected function restoreWholesaleSessionFromOrder(Order $order): void
    {
        $raw = $order->wholesale_delivery_date ?? null;
        if (!$raw) {
            return;
        }
        try {
            $date = $raw instanceof \Carbon\Carbon
                ? $raw->toDateString()
                : \Carbon\Carbon::parse((string) $raw)->toDateString();
        } catch (\Throwable $e) {
            $date = substr((string) $raw, 0, 10);
        }
        if ($date) {
            Session::put('wholesale_delivery_date', $date);
            Session::put('selected_order_type', 'wholesale');
        }
    }

    protected function findWholesaleMenu()
    {
        $query = Menu::query()->orderBy('id');
        $hasType = Schema::hasColumn('menus', 'type');
        $hasSlug = Schema::hasColumn('menus', 'slug');

        if ($hasType || $hasSlug) {
            return $query->where(function ($q) use ($hasType, $hasSlug) {
                if ($hasType) {
                    $q->where('type', 'wholesale');
                }
                if ($hasSlug) {
                    $q->orWhere('slug', 'dessert-wholesale');
                }
                $q->orWhere('name', 'like', '%Wholesale%');
            })->first();
        }

        return $query->where('name', 'like', '%Wholesale%')->first();
    }

    protected function foodProducts()
    {
        $query = Product::with(['variants', 'menu'])->where('status', 1);

        if (Schema::hasColumn('menus', 'type')) {
            $query->whereHas('menu', function ($q) {
                $q->where(function ($inner) {
                    $inner->whereNull('type')->orWhere('type', 'food');
                });
            });
        }

        return $query->orderByDesc('is_featured')->take(24)->get();
    }
}
