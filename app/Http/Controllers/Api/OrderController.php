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

    $orders = Order::where('user_id', $user->id)
        ->where('status', $status)
        ->latest()
        ->get();

    if ($orders->isEmpty()) {
        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => [],
                'orders' => [],
            ]
        ]);
    }

    $lifecycle = app(\App\Services\OrderLifecycleService::class);
    $allItems = collect();

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

                // =========================
                // TOPPINGS
                // =========================
                $toppings = $item->orderToppings->map(function ($t) {
                    return [
                        'category_name' => $t->category->name ?? null,
                        'topping_name'  => $t->toppings->name ?? null
                    ];
                });

                // =========================
                // COMPLEMENTARY PRODUCT (NEW)
                // =========================
                $complementaryProduct = null;

                if (!empty($item->product_complementary_id)) {

                    $comp = DB::table('products')
                        ->where('id', $item->product_complementary_id)
                        ->first();

                    if ($comp) {
                        $complementaryProduct = [
                            'id'    => $comp->id,
                            'name'  => $comp->name,
                            'image' => $comp->image,
                        ];
                    }
                }

                return [
                    'order_id'         => $order->id,
                    'order_code'       => $order->code,
                    'order_item_id'    => $item->id,
                    'product_id'       => $item->product_id,
                    'product_name'     => $item->product_name,
                    'product_image'    => $item->product->image ?? null,
                    'product_size'     => $item->product_size,
                    'product_price'    => $item->product_price,
                    'product_original_price' => $item->original_price,
                    'quantity'         => $item->quantity,
                    'delivery_address' => $item->delivery_address,
                    'order_type'       => $item->order_type ?: $order->order_type,
                    'channel'          => $order->channelKey(),
                    'channel_label'    => $order->channelLabel(),
                    'fulfillment_label'=> \App\Support\CartCheckout::fulfillmentLabel($item, $order->order_type),
                    'wholesale_delivery_date' => $order->wholesale_delivery_date,
                    'receipt_print_url'=> url('/api/orders/' . $order->id . '/receipt?html=1'),
                    'can_modify'       => $lifecycle->canModify($order),
                    'toppings'         => $toppings,

                    // ✅ NEW FIELD
                    'complementary_product' => $complementaryProduct,
                ];
            });

    $allItems = $allItems->merge($orderItems);
    }

    $primary = $orders->first();
    $state = $lifecycle->publicState($primary);
    $orderCards = $orders->map(function ($order) use ($lifecycle) {
        return [
            'id' => $order->id,
            'code' => $order->code,
            'status' => $order->status,
            'channel' => $order->channelKey(),
            'channel_label' => $order->channelLabel(),
            'total_amount' => $order->total_amount,
            'discount_amount' => $order->discount_amount,
            'discount_label' => $order->discount_label,
            'wholesale_delivery_date' => $order->wholesale_delivery_date,
            'can_modify' => $lifecycle->canModify($order),
            'receipt_print_url' => url('/api/orders/' . $order->id . '/receipt?html=1'),
            'receipt_json_url' => url('/api/orders/' . $order->id . '/receipt'),
            'state' => $lifecycle->publicState($order),
        ];
    });

    if (in_array($status, ['Pending', 'Order Ready', 'Scheduled'])) {
        return response()->json([
            'status' => 'success',
            'data' => array_merge($state, [
                'items' => $allItems,
                'orders' => $orderCards,
            ])
        ]);
    }

    if ($status === 'Delivered') {

        $branchId = $primary?->orderItem?->first()?->branch_id;
        $branch   = $branchId ? Branch::find($branchId) : null;

        return response()->json([
            'status' => 'success',
            'data' => [
                'message'          => 'Your order has been placed successfully.',
                'total_amount'     => $primary->total_amount ?? 0,
                'estimated_tax'    => $branch->tax ?? 0,
                "order_code"       => $primary->code,
                'estimated_amount' => $primary->estimated_total ?? 0,
                'order_type'       => $primary?->order_type ?: $primary?->orderItem?->first()?->order_type,
                'channel'          => $primary?->channelKey(),
                'channel_label'    => $primary?->channelLabel(),
                'delivery_address' => $primary?->orderItem?->first()?->delivery_address,
                'items'            => $allItems,
                'orders'           => $orderCards,
            ]
        ]);
    }

    return response()->json([
        'status' => 'success',
        'data' => [
            'items' => $allItems,
            'orders' => $orderCards,
        ]
    ]);
}






}
