<?php

namespace App\Http\Controllers\Api;

use DB;
use Exception;
use App\Models\Order;
use App\Models\Branch;
use App\Models\Reward;
use App\Models\Topping;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Models\OrderItemToppings;
use App\Http\Controllers\Controller;

class PlaceOrderController extends Controller
{
    //

public function placeOrder(Request $request)
{
    try {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $userId = $user->id;

        DB::beginTransaction();

        // 1️⃣ Fetch cart items
        $cartItems = DB::table('add_to_cart_items')
            ->where('user_id', $userId)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No items in cart to place order.'
            ], 400);
        }

        // 2️⃣ Create Order
        $order = new Order();
        $order->code = random_int(10000000, 99999999);
        $order->user_id = $userId;
        $order->product_id = $cartItems->first()->product_id;
        $order->status = 'Pending';
        $order->payment = 'offline';
        $order->vehicle_color = $request->vehicle_color ?? null;
        $order->vehicle_number = $request->vehicle_number ?? null;
        $order->subtotal = $cartItems->first()->subtotal ?? 0;
        $order->tax = $cartItems->first()->tax_amount ?? 0;
        $order->estimated_total = $cartItems->first()->estimated_total ?? 0;
        $order->total_amount = $cartItems->first()->estimated_total ?? 0;
        $order->save();

        $orderId = $order->id;

        // 3️⃣ Product Variant
        $productVariant = DB::table('product_variants')
            ->where('product_id', $cartItems->first()->product_id)
            ->first();

        // 4️⃣ Save Order Items + Toppings
        foreach ($cartItems as $item) {

            $orderItem = new OrderItem();
            $orderItem->order_id = $orderId;
            $orderItem->product_id = $item->product_id;
            $orderItem->product_name = $item->product_name;
            $orderItem->branch_id = $item->branch_id;
            $orderItem->quantity = $item->quantity;
            $orderItem->product_size = $productVariant->size ?? 'Default';
            $orderItem->product_price = $item->price;
            $orderItem->tip = $item->tips ?? 0;
            $orderItem->tax = $item->tax_amount ?? 0;
            $orderItem->delivery_address = $item->delivery_address;
            $orderItem->order_type = $item->order_type;
            $orderItem->sub_total = $item->subtotal ?? 0;
            $orderItem->save();

            // Save toppings
            $toppings = DB::table('add_to_cart_item_toppings')
                ->where('add_to_cart_item_id', $item->id)
                ->whereNotNull('topping_id')
                ->get();

            foreach ($toppings as $topping) {
                $orderItemTopping = new OrderItemToppings();
                $orderItemTopping->order_item_id = $orderItem->id;
                $orderItemTopping->category_id = $topping->category_id;
                $orderItemTopping->topping_id = $topping->topping_id;
                $orderItemTopping->save();
            }
        }

        // 5️⃣ Clear Cart
        DB::table('add_to_cart_item_toppings')
            ->whereIn('add_to_cart_item_id', $cartItems->pluck('id'))
            ->delete();

        DB::table('add_to_cart_items')
            ->where('user_id', $userId)
            ->delete();

        DB::commit();

        // ===============================
        // 🔔 SAVE NOTIFICATION IN DATABASE
        // ===============================
        $title = 'Order Placed Successfully!';
        $description = "Your order #{$order->code} has been placed successfully.";

        \App\Models\Notification::create([
            'user_id'     => $userId,
            'title'       => $title,
            'description' => $description,
            'seenByUser'  => 0,
        ]);

        // ===============================
        // 🔔 PUSH NOTIFICATION (APP ONLY)
        // ===============================
        if ($user->fcmtoken) {
            dispatch(new \App\Jobs\JobNotification(
                $user->fcmtoken,
                $title,
                $description,
                [
                    'order_id' => $orderId,
                    'status'   => 'Pending'
                ]
            ));
        }

        return response()->json([
            'status'   => true,
            'message' => 'Order placed successfully!',
            'order_id'=> $orderId
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}





}
