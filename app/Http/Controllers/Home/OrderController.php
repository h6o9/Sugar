<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirm;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderComplationReward;
use App\Models\OrderItem;
use App\Models\OrderItemToppings;
use App\Models\OrderCompletionRecord;
use App\Models\Reward;
use App\Models\RewardHistory;
use App\Models\Topping;
use App\Models\User;
use App\Support\CartCheckout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Log;
use Square\Environment;
use Square\Exceptions\ApiException;
use Square\Models\CreatePaymentRequest;
use Square\Models\Money;
use Square\SquareClient;
use App\Jobs\JobNotification;
use App\Models\Notification;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class OrderController extends Controller
{
    public function myOrder()
    {
        $userId = Auth::guard('user')->id();
        $lifecycle = app(\App\Services\OrderLifecycleService::class);
        if ($userId) {
            $lifecycle->reattachOrphanItemsForUser((int) $userId);
        }
        $orders = \App\Models\Order::with([
                'orderItem.complementaryProduct',
                'orderItem.branch',
                'orderItem.product',
                'orderItem.orderToppings.category',
                'orderItem.orderToppings.toppings',
            ])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        $timerOrders = $orders->filter(function ($order) use ($lifecycle) {
            return $lifecycle->canModify($order) && $lifecycle->remainingSeconds($order) > 0;
        })->values();
        // #region agent log
        $firstTimer = $timerOrders->first();
        $latest = $orders->first();
        file_put_contents(base_path('debug-1796d5.log'), json_encode([
            'sessionId' => '1796d5',
            'hypothesisId' => 'W1',
            'location' => 'OrderController.php:myOrder',
            'message' => 'latest vs timer order types',
            'data' => [
                'latest_code' => $latest->code ?? null,
                'latest_order_type' => $latest->order_type ?? null,
                'latest_menu_type' => $latest->menu_type ?? null,
                'latest_wholesale_date' => $latest->wholesale_delivery_date ?? null,
                'latest_can_modify' => $latest ? $lifecycle->canModify($latest) : null,
                'timer_code' => $firstTimer->code ?? null,
                'timer_order_type' => $firstTimer->order_type ?? null,
                'session_order_type' => session('selected_order_type'),
            ],
            'timestamp' => (int) round(microtime(true) * 1000),
        ]) . "\n", FILE_APPEND);
        // #endregion

        $pendingOrders = $orders->filter(function ($order) use ($lifecycle) {
            if ($lifecycle->canModify($order) && $lifecycle->remainingSeconds($order) > 0) {
                return false;
            }
            $status = strtolower((string) $order->status);
            return !in_array($status, ['delivered', 'cancelled', 'canceled', 'completed'], true);
        })->values();

        $pastOrders = $orders->filter(function ($order) {
            $status = strtolower((string) $order->status);
            return in_array($status, ['delivered', 'cancelled', 'canceled', 'completed'], true);
        })->values();

        return view('home.my-orders', compact('timerOrders', 'pendingOrders', 'pastOrders'));
    }


      public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found']);
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Update order status
        $order->status = $newStatus;
        $order->save();

        // Get user for notification
        $user = User::find($order->user_id);

        // Send notification based on status change
        if ($user && $user->fcmtoken) {
            $title = '';
            $description = '';
            $data = ['order_id' => $order->id, 'order_code' => $order->code];

            switch ($newStatus) {
                case 'Order Ready':
                    $title = '🍔 Your Order is Ready!';
                    $description = "Your order #{$order->code} is ready for pickup/delivery.";
                    $data['status'] = 'order_ready';
                    $data['screen_name'] = 'MyOrders';
                    break;

                case 'Delivered':
                    // Process rewards for delivered order
                    $rewardPoints = 0;
                    $totalRewards = 0;
                    
                    try {
                        DB::transaction(function () use ($order, &$rewardPoints, &$totalRewards) {
                            $rewardConfig = OrderComplationReward::first();
                            $rewardPoints = $rewardConfig?->points ?? 0;

                            // Check if already rewarded
                            $orderRewardExists = OrderCompletionRecord::where('order_id', $order->id)->exists();

                            if (!$orderRewardExists && $rewardPoints > 0) {
                                // Order completion record
                                OrderCompletionRecord::create([
                                    'order_id'    => $order->id,
                                    'order_code'  => $order->code ?? null,
                                    'reward_type' => 'order_completion',
                                    'points'      => $rewardPoints,
                                ]);

                                // Reward history
                                RewardHistory::create([
                                    'reward_type'   => 'order_completion',
                                    'reward_title'  => 'Order Points Added!',
                                    'points'        => $rewardPoints,
                                    'user_id'       => $order->user_id,
                                    'description'   => 'You have earned ' . $rewardPoints . ' points for completing recent order.',
                                    'order_code'    => $order->code ?? null,
                                    'referral_code' => $order->referral_code ?? null,
                                ]);

                                // Update rewards table
                                $reward = Reward::firstOrCreate(
                                    ['user_id' => $order->user_id],
                                    ['rewards' => 0, 'redeemed' => 0]
                                );

                                $reward->increment('rewards', $rewardPoints);
                                $totalRewards = $reward->rewards;
                            }
                        });
                    } catch (\Exception $e) {
                        \Log::error('Reward processing error for order ' . $order->code . ': ' . $e->getMessage());
                    }

                    // Create notification with reward info if applicable
                    if ($rewardPoints > 0 && $totalRewards > 0) {
                        $title = '🎉 Order Delivered & Reward Earned!';
                        $description = "Your order #{$order->code} has been delivered successfully. You earned {$rewardPoints} reward points! Total rewards: {$totalRewards}";
                        $data['status'] = 'order_delivered_with_reward';
                        $data['reward_points'] = $rewardPoints;
                        $data['total_rewards'] = $totalRewards;
                         $data['screen_name'] = 'MyOrders'; 
                        
                    } else {
                        $title = '✅ Order Delivered!';
                        $description = "Your order #{$order->code} has been delivered successfully.";
                        $data['status'] = 'order_delivered';
                        $data['screen_name'] = 'MyOrders'; 

                    }
                    $data['screen_name'] = 'MyOrders'; 
                    break;

                case 'Out for Delivery':
                    $title = '🚚 Order Out for Delivery!';
                    $description = "Your order #{$order->code} is out for delivery.";
                    $data['status'] = 'out_for_delivery';
                    $data['screen_name'] = 'MyOrders';
                    break;

                default:
                    return response()->json(['status' => false, 'message' => 'Invalid status']);
            }

            // Send push notification
            dispatch(new JobNotification($user->fcmtoken, $title, $description, $data));

            // Save notification in database
            Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'description' => $description,
                'seenByUser' => 0,
            ]);
        }

        return redirect()->back()->with([
            'status' => true, 
            'message' => 'Order status updated successfully'
        ]);
    }


   
    public function order(Request $request)
    {
        $looksWholesale = $this->cartLooksWholesale();
        $selectedCart = CartCheckout::selected();
        // #region agent log
        file_put_contents(base_path('debug-1796d5.log'), json_encode([
            'sessionId' => '1796d5',
            'hypothesisId' => 'W4',
            'location' => 'OrderController.php:order',
            'message' => 'place order wholesale check',
            'data' => [
                'cartLooksWholesale' => $looksWholesale,
                'session_order_type' => session('selected_order_type'),
                'has_wholesale_date' => (bool) session('wholesale_delivery_date'),
                'cart_count' => count(session('cart', [])),
                'selected_count' => count($selectedCart),
                'fulfillments' => array_values(array_map(function ($item) {
                    return \App\Support\CartCheckout::fulfillmentLabel($item, session('selected_order_type'));
                }, $selectedCart)),
            ],
            'timestamp' => (int) round(microtime(true) * 1000),
        ]) . "\n", FILE_APPEND);
        // #endregion
        if ($redirect = $this->rejectInvalidWholesaleSession()) {
            return $redirect;
        }
        DB::beginTransaction();
        try {
            $user = Auth::guard('user')->user();
            $userId = $user->id;
            $products = CartCheckout::selected();
            if (empty($products)) {
                DB::rollBack();
                return redirect()->route('my-cart')->with(['status' => false, 'message' => 'Please select at least one item to place the order.']);
            }
            $vehicle_color = session('vehicle_color', []);
            $vehicle_number = session('vehicle_number', []);
            $redeemedAmount = session('redeem_amount', []);
            $redeemedPoints = session('redeem_points', []);
            $dateTime = session('time', []);
            $startTime = session('start_time', []);
            $tip_amount = session('tip_amount', []);
            $orderTotal = session('orderTotal', []);
            $deliveryCharge = session('delivery_charge', 0);
            $tipValue = is_array($tip_amount) ? floatval(array_sum($tip_amount)) : floatval($tip_amount ?: 0);
            $redeemValue = is_array($redeemedAmount) ? floatval(array_sum($redeemedAmount)) : floatval($redeemedAmount ?: 0);
            $redeemPointsValue = is_array($redeemedPoints) ? floatval(array_sum($redeemedPoints)) : floatval($redeemedPoints ?: 0);
            $total = 0;
            $branchId = null;
            foreach ($products as $id => $details) {
                $branchId = $details['branch_id'] ?? $branchId;
            }

            $order = new Order();
            $order->code = random_int(10000000, 99999999);
            $order->user_id = $userId;
            $order->vehicle_color = $vehicle_color ?: 'NULL';
            $order->vehicle_number = $vehicle_number ?: 'NULL';
            $order->redeemed = $redeemValue;
            $order->redeemed_points = $redeemPointsValue;
            $order->status = 'Pending';
            $order->payment = 'offline';
            $order->date = $dateTime['date'] ?? null;
            $order->time = $dateTime['time'] ?? $startTime;

            foreach ($products as $id => $details) {
                $total += floatval($details['price']) * floatval($details['quantity']);
            }

            $branch = Branch::find($branchId);
            $tax = $branch && $branch->status == 1 ? $branch->tax : 0;
            $order->total_amount = $total;
            $order->delivery_charge = $deliveryCharge;
            $order->save();

            $orderId = $this->resolvedOrderId($order, $userId);

            // ✅ Save order items and toppings
            foreach ($products as $id => $details) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $orderId;
                $orderItem->product_id = $details['product_id'];
                $orderItem->product_complementary_id = $details['complementary']['id'] ?? null;
                $orderItem->product_size = $details['size'] ?? 'NULL';
                $orderItem->product_price = $details['price'];
                $orderItem->branch_id = $details['branch_id'];
                $orderItem->product_name = $details['name'];
                $orderItem->quantity = $details['quantity'];
                $orderItem->tip = $tipValue;
                $orderItem->sub_total = floatval($details['price']) * floatval($details['quantity']);
                $orderItem->delivery_status = $details['delivery_status'] ?? null;
                $orderItem->delivery_address = $details['delivery_address'] ?? null;
                
                $orderItem->save();

                if (isset($details['toppings_by_category'])) {
                    foreach ($details['toppings_by_category'] as $categoryId => $toppingIds) {
                        foreach ($toppingIds as $toppingId) {
                            $orderItemTopping = new OrderItemToppings();
                            $orderItemTopping->order_item_id = $orderItem->id;
                            $orderItemTopping->topping_id = $toppingId;
                            $orderItemTopping->category_id = $categoryId;
                            $orderItemTopping->save();

                            $topping = Topping::find($toppingId);
                            if ($topping) {
                                $total += $topping->price;
                            }
                        }
                    }
                }
            }

            $order->total_amount = $total + $tipValue + $tax + $deliveryCharge - $redeemValue;
            $order->save();

            $points = $order->total_amount;
            if ($user) {
                $existingPoints = $user->point;
                $totalPoints = floor($existingPoints + $points);
                $user->update(['point' => $totalPoints]);
            }

            $reward = Reward::where('user_id', $user->id)->first();
            if ($reward) {
               $reward->update([
                    'rewards' => $reward->rewards - $redeemPointsValue,
                    'redeemed' => $redeemPointsValue + ($reward->redeemed ?? 0),
                ]);
            }

            $orderCode = $order->code;
            $orderType = session('selected_order_type', 'standard');
            $lifecycle = app(\App\Services\OrderLifecycleService::class);
            try {
                $lifecycle->initializeNewOrder($order, [
                    'order_type' => $orderType,
                    'menu_type' => $orderType === 'wholesale' ? 'wholesale' : 'food',
                    'is_scheduled' => session('scheduled_order') ? 1 : 0,
                    'scheduled_at' => session('scheduled_at'),
                    'wholesale_delivery_date' => session('wholesale_delivery_date'),
                ]);
            } catch (\Throwable $e) {
                \Log::warning('initializeNewOrder skipped after place order', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
            if (strtolower((string) $order->payment) !== 'stripe') {
                try {
                    $lifecycle->recordPayment($order, 0, 'offline', 'original', ['status' => 'unpaid', 'notes' => 'Pay on collection/delivery']);
                } catch (\Throwable $e) {
                    \Log::warning('recordPayment skipped after place order', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                }
            }
            DB::commit();
            CartCheckout::forgetPlaced();
            session()->forget([
                'tip_amount', 'redeem_points', 'redeem_amount',
                'vehicle_color', 'vehicle_number', 'time', 'start_time',
                'delivery_charge', 'selected_order_type', 'scheduled_order',
                'scheduled_at', 'wholesale_delivery_date', 'adding_to_order_id',
            ]);
            session(['active_add_order_id' => $order->id]);
            return redirect()->route('my-order')->with(['status' => true, 'message' => 'Order placed successfully. You can see your order in My Orders.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

  public function stripePayment()
{
    $finalTotal = session('orderTotal');

    $gatewayFee = ($finalTotal * 0.025) + 0.25;

    $gatewayFee = round($gatewayFee, 2);
    $finalTotalWithFee = round($finalTotal + $gatewayFee, 2);
    

    Stripe::setApiKey(config('services.stripe.secret'));

    $session = StripeSession::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'gbp',
                'product_data' => [
                    'name' => 'Order Payment',
                ],
                'unit_amount' => (int) round($finalTotalWithFee * 100), // cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => route('stripe.cancel'),
    ]);

    return redirect($session->url);
}
public function stripeSuccess()
{
    $sessionId = request()->get('session_id');

    if (!$sessionId) {
        return redirect()->route('checkout')->with('error', 'Invalid payment');
    }

    \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

    $session = \Stripe\Checkout\Session::retrieve($sessionId);

    // ❌ stop if not paid
    if ($session->payment_status !== 'paid') {
        return redirect()->route('checkout')->with('error', 'Payment not completed');
    }
    if ($redirect = $this->rejectInvalidWholesaleSession()) {
        return $redirect;
    }
     DB::beginTransaction();
        try {
            $user = Auth::guard('user')->user();
            $userId = $user->id;
            $products = CartCheckout::selected();
            if (empty($products)) {
                DB::rollBack();
                return redirect()->route('my-cart')->with(['status' => false, 'message' => 'Please select at least one item to place the order.']);
            }
            $vehicle_color = session('vehicle_color', []);
            $vehicle_number = session('vehicle_number', []);
            $redeemedAmount = session('redeem_amount', []);
            $redeemedPoints = session('redeem_points', []);
            $dateTime = session('time', []);
            $startTime = session('start_time', []);
            $tip_amount = session('tip_amount', []);
            $orderTotal = session('orderTotal', []);
            $deliveryCharge = session('delivery_charge', 0);
            $tipValue = is_array($tip_amount) ? floatval(array_sum($tip_amount)) : floatval($tip_amount ?: 0);
            $redeemValue = is_array($redeemedAmount) ? floatval(array_sum($redeemedAmount)) : floatval($redeemedAmount ?: 0);
            $redeemPointsValue = is_array($redeemedPoints) ? floatval(array_sum($redeemedPoints)) : floatval($redeemedPoints ?: 0);
            $total = 0;
            $branchId = null;
            foreach ($products as $id => $details) {
                $branchId = $details['branch_id'] ?? $branchId;
            }

            $order = new Order();
            $order->code = random_int(10000000, 99999999);
            $order->user_id = $userId;
            $order->vehicle_color = $vehicle_color ?: 'NULL';
            $order->vehicle_number = $vehicle_number ?: 'NULL';
            $order->redeemed = $redeemValue;
            $order->redeemed_points = $redeemPointsValue;
            $order->status = 'Pending';
            $order->payment = 'stripe';
            $order->date = $dateTime['date'] ?? null;
            $order->time = $dateTime['time'] ?? $startTime;

            foreach ($products as $id => $details) {
                $total += floatval($details['price']) * floatval($details['quantity']);
            }

            $branch = Branch::find($branchId);
            $tax = $branch && $branch->status == 1 ? $branch->tax : 0;
            $order->total_amount = $total;
            $order->delivery_charge = $deliveryCharge;
            $order->save();

            $orderId = $this->resolvedOrderId($order, $userId);

            foreach ($products as $id => $details) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $orderId;
                $orderItem->product_id = $details['product_id'];
                $orderItem->product_complementary_id = $details['complementary']['id'] ?? null;
                $orderItem->product_size = $details['size'] ?? 'NULL';
                $orderItem->product_price = $details['price'];
                $orderItem->branch_id = $details['branch_id'];
                $orderItem->product_name = $details['name'];
                $orderItem->quantity = $details['quantity'];
                $orderItem->tip = $tipValue;
                $orderItem->sub_total = floatval($details['price']) * floatval($details['quantity']);
                $orderItem->delivery_status = $details['delivery_status'] ?? null;
                $orderItem->delivery_address = $details['delivery_address'] ?? null;

                $orderItem->save();

                if (isset($details['toppings_by_category'])) {
                    foreach ($details['toppings_by_category'] as $categoryId => $toppingIds) {
                        foreach ($toppingIds as $toppingId) {
                            $orderItemTopping = new OrderItemToppings();
                            $orderItemTopping->order_item_id = $orderItem->id;
                            $orderItemTopping->topping_id = $toppingId;
                            $orderItemTopping->category_id = $categoryId;
                            $orderItemTopping->save();

                            $topping = Topping::find($toppingId);
                            if ($topping) {
                                $total += $topping->price;
                            }
                        }
                    }
                }
            }

            $finalTotal = $total + $tipValue + $tax + $deliveryCharge - $redeemValue;
            $gatewayFee = ($finalTotal * 0.025) + 0.25;

            $order->total_amount = $finalTotal + $gatewayFee;
            $order->gateway_fee = $gatewayFee;
            $order->save();

            $points = $order->total_amount;
            if ($user) {
                $existingPoints = $user->point;
                $totalPoints = floor($existingPoints + $points);
                $user->update(['point' => $totalPoints]);
            }

            $reward = Reward::where('user_id', $user->id)->first();
            if ($reward) {
               $reward->update([
                    'rewards' => $reward->rewards - $redeemPointsValue,
                    'redeemed' => $redeemPointsValue + ($reward->redeemed ?? 0),
                ]);
            }

            $orderType = session('selected_order_type', 'standard');
            $lifecycle = app(\App\Services\OrderLifecycleService::class);
            try {
                $lifecycle->initializeNewOrder($order, [
                    'order_type' => $orderType,
                    'menu_type' => $orderType === 'wholesale' ? 'wholesale' : 'food',
                    'is_scheduled' => session('scheduled_order') ? 1 : 0,
                    'scheduled_at' => session('scheduled_at'),
                    'wholesale_delivery_date' => session('wholesale_delivery_date'),
                ]);
            } catch (\Throwable $e) {
                \Log::warning('initializeNewOrder skipped after stripe order', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
            try {
                $lifecycle->recordPayment($order, floatval($order->total_amount), 'stripe', 'original', [
                    'stripe_session_id' => $sessionId,
                    'status' => 'paid',
                ]);
            } catch (\Throwable $e) {
                \Log::warning('recordPayment skipped after stripe order', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
            DB::commit();
            CartCheckout::forgetPlaced();
            session()->forget([
                'tip_amount', 'redeem_points', 'redeem_amount',
                'vehicle_color', 'vehicle_number', 'time', 'start_time',
                'delivery_charge', 'selected_order_type', 'scheduled_order',
                'scheduled_at', 'wholesale_delivery_date', 'adding_to_order_id',
            ]);
            session(['active_add_order_id' => $order->id]);
            return redirect()->route('my-order')->with(['status' => true, 'message' => 'Order placed successfully. You can see your order in My Orders.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
}

    private function getAccessToken($branchId)
    {
        $accessTokens = [
            7 => 'EAAAlhmrLWxke5X0wF3NnZvofLP5cqKzaYuhDP0o9XwRkQ3sy1wBfdu8BCEP7hbT',
            6 => 'EAAAljgj_zgkKeCYGCHJ5lnAIKLR_X3kfT5pXxHREQSZNARu-5O3K1qRLfAr1i9e',
            8 => 'EAAAlo_Lee_l3Du915VXyW9fQGm9N99wLKfuRQFn9QzdTLOnuh2MsMEhP0sL2hi4',
        ];
        return $accessTokens[$branchId] ?? 'EAAAlt7VHkCQ7YGtAJyDwAw1Of0nnrIvF5JwU8AyTuf_YA1Y8pJbEXbwqMSyfFBs';
    }
    public function markAllAsRead(Request $request)
    {
        $notification = Order::find($request->id);

        if ($notification) {
            $notification->seen = 1;
            $notification->save();
        }
    }

    private function cartLooksWholesale(): bool
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return session('selected_order_type') === 'wholesale' && (bool) session('wholesale_delivery_date');
        }
        foreach ($cart as $item) {
            if (($item['fulfillment'] ?? '') === 'wholesale') {
                return true;
            }
        }
        $ids = [];
        foreach ($cart as $item) {
            if (!empty($item['product_id'])) {
                $ids[] = (int) $item['product_id'];
            }
        }
        if (!$ids) {
            return session('selected_order_type') === 'wholesale';
        }
        try {
            $products = \App\Models\Product::with('menu')->whereIn('id', $ids)->get();
        } catch (\Throwable $e) {
            return session('selected_order_type') === 'wholesale';
        }
        foreach ($products as $product) {
            $menu = $product->menu;
            if (!$menu) {
                continue;
            }
            $type = strtolower((string) ($menu->type ?? ''));
            $slug = strtolower((string) ($menu->slug ?? ''));
            if ($type === 'wholesale' || $slug === 'dessert-wholesale') {
                return true;
            }
        }
        return session('selected_order_type') === 'wholesale';
    }

    private function rejectInvalidWholesaleSession()
    {
        if (!$this->cartLooksWholesale()) {
            return null;
        }
        Session::put('selected_order_type', 'wholesale');
        $date = session('wholesale_delivery_date');
        $wholesale = app(\App\Services\WholesaleScheduleService::class);
        if (!$date || !$wholesale->isValidDate($date)) {
            return redirect()->route('dessert-wholesale')->with([
                'status' => false,
                'message' => 'Please select a wholesale delivery date before placing this order.',
            ]);
        }
        return null;
    }

    private function resolvedOrderId(Order $order, $userId): int
    {
        $id = (int) $order->getKey();
        if ($id > 0) {
            return $id;
        }
        $found = Order::where('code', $order->code)
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->first();
        if ($found) {
            $order->id = $found->id;
            $order->exists = true;
            return (int) $found->id;
        }
        return 0;
    }
}
