<?php

namespace App\Http\Controllers\Api;

use Log;
use Exception;
use App\Models\User;
use App\Models\Order;
use App\Models\Branch;
use App\Models\Reward;
use App\Models\OrderItem;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Jobs\JobNotification;
use App\Models\RewardHistory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\OrderComplationReward;
use App\Models\OrderCompletionRecord;

class OrderController extends Controller
{
    //

public function myOrders(Request $request)
{
    $user   = auth()->user();
    $status = $request->status;

    // 🟢 Orders fetch
    $orders = Order::where('user_id', $user->id)
        ->where('status', $status)
        ->latest()
        ->get();

    // 🟢 No orders
    if ($orders->isEmpty()) {
        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => []
            ]
        ]);
    }

    $allItems = collect();

    // 🟢 Loop orders
    foreach ($orders as $order) {

        $orderItems = OrderItem::where('order_id', $order->id)
            ->with([
                'product:id,name,image',
                'orderToppings' => function ($q) {
                    $q->with([
                        'category:id,name',
                        'toppings:id,name'
                    ]);
                }
            ])
            ->get()
            ->map(function ($item) {

                $toppings = $item->orderToppings->map(function ($t) {
                    return [
                        'category_name' => $t->category->name ?? null,
                        'topping_name'  => $t->toppings->name ?? null
                    ];
                });

                return [
                    'product_id'       => $item->product_id,
                    'product_name'     => $item->product_name,
                    'product_image'    => $item->product->image ?? null,
                    'product_size'     => $item->product_size,
                    'product_price'    => $item->product_price,
                    'delivery_address' => $item->delivery_address,
                    'order_type'       => $item->order_type,
                    'toppings'         => $toppings,
                ];
            });

        $allItems = $allItems->merge($orderItems);
    }

    $order = $orders->first();

    // 🔴 PENDING / ORDERREADY → ITEMS ONLY
    if (in_array($status, ['Pending', 'Order Ready'])) {
        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $allItems
            ]
        ]);
    }

    // 🟢 DELIVERED → FULL DETAILS + REWARDS
    if ($status === 'Delivered') {

        try {

            DB::transaction(function () use ($orders) {

                $rewardConfig = OrderComplationReward::first();
                $rewardPoints = $rewardConfig?->points ?? 0;

                foreach ($orders as $deliveredOrder) {

                    // ❌ Already rewarded order check
                    $orderRewardExists = OrderCompletionRecord::where(
                        'order_id',
                        $deliveredOrder->id
                    )->exists();

                    if ($orderRewardExists) {
                        continue;
                    }

                    // 🟢 Order completion record
                    OrderCompletionRecord::create([
                        'order_id'    => $deliveredOrder->id,
                        'order_code'  => $deliveredOrder->code ?? null,
                        'reward_type' => 'order_completion',
                        'points'      => $rewardPoints,
                    ]);

                    // ❌ Reward history duplicate check
                    $rewardHistoryExists = RewardHistory::where('order_code', $deliveredOrder->code)
                        ->exists();

                    if (!$rewardHistoryExists) {

                        RewardHistory::create([
                            'reward_type'   => 'order_completion',
                            'reward_title'  => 'Order Points Added!',
                            'points'        => $rewardPoints,
                            'user_id'       => $deliveredOrder->user_id,
                            'description'   => 'You have earned ' . $rewardPoints . ' points for completing recent order.',
                            'order_code'    => $deliveredOrder->code ?? null,
                            'referral_code' => $deliveredOrder->referral_code ?? null,
                        ]);
                    }

                    // 🟢 Update rewards table
                    $reward = Reward::firstOrCreate(
                        ['user_id' => $deliveredOrder->user_id],
                        ['rewards' => 0, 'redeemed' => 0]
                    );

                    $reward->increment('rewards', $rewardPoints);

                    // 🔔 PUSH NOTIFICATION
                    $user = User::find($deliveredOrder->user_id);

                    if ($user && $user->fcm) {

                        dispatch(new JobNotification(
                            $user->fcm,
                            '🎉 Order Completed!',
                            "You earned {$rewardPoints} reward points for completing your order.",
                            [
                                'type'       => 'order_completion',
                                'points'     => $rewardPoints,
                                'order_code' => $deliveredOrder->code,
                            ]
                        ));
                    }

                    // 🗂 Save notification in DB
                    Notification::create([
                        'user_id'     => $deliveredOrder->user_id,
                        'title'       => '🎉 Order Completed!',
                        'description' => "You earned {$rewardPoints} reward points for completing your order.",
                        'seenByUser'  => 0,
                    ]);
                }
            });

        } catch (\Exception $e) {
            Log::error('Order reward error: ' . $e->getMessage());
        }

        $branchId = $order?->orderItem?->first()?->branch_id;
        $branch   = $branchId ? Branch::find($branchId) : null;

        return response()->json([
            'status' => 'success',
            'data' => [
                'message'            => 'Your order has been placed successfully.',
                'total_amount'       => $order->total_amount ?? 0,
                'estimated_tax'      => $branch->tax ?? 0,
                'estimated_amount'   => $order->estimated_total ?? 0,
                'order_type'         => $order?->orderItem?->first()?->order_type,
                'delivery_address'   => $order?->orderItem?->first()?->delivery_address,
                'items'              => $allItems,
            ]
        ]);
    }

    // 🟡 Fallback
    return response()->json([
        'status' => 'success',
        'data' => [
            'items' => $allItems
        ]
    ]);
}







}
