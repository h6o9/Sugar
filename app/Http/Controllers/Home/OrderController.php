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
use App\Models\Reward;
use App\Models\Topping;
use App\Models\User;
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
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class OrderController extends Controller
{
    public function myOrder()
    {
        $orders = OrderItem::with([
            'complementaryProduct',
            'branch',
            'product.variants',
            'order.user',
            'orderToppings.category',
            'orderToppings.toppings'
        ])->latest()->get();
        // return $orders;
        return view('home.my-orders', compact('orders'));
    }
    // public function updateStatus(Request $request, $
	// 
	// Id)
    // {
    //     $request->validate([
    //         'status' => 'required|in:Pending,Order Ready,Delivered',
    //     ]);
    //     $order = Order::find($orderId);
    //     if (!$order) {
    //         return redirect()->back()->with(['status' => false, 'message' => 'Order Updated UnSuccessfully']);
    //     }
    //     $order->status = $request->status;
    //     $order->save();
    //     return redirect()->back()->with(['status' => true, 'message' => 'Order Updated Successfully']);
    // }
    // public function updateStatus(Request $request, $orderId)
    // {
    //     $request->validate([
    //         'status' => 'required|in:Pending,Order Ready,Delivered',
    //     ]);
    //     $order = Order::find($orderId);
    //     if (!$order) {
    //         return redirect()->back()->with(['status' => false, 'message' => 'Order Updated Unsuccessfully']);
    //     }
    //     $oldStatus = $order->status;
    //     $newStatus = $request->status;
    //     $order->status = $newStatus;
    //     $order->save();
    //     if ($oldStatus !== $newStatus && $newStatus === 'Order Ready') {
    //         $user = $order->user;
    //         if ($user) {
    //             $newFcmToken = $user->fcmtoken;
    //             $notificationData = [
    //                 'title' => 'Your Order Notification',
    //                 'body' => 'Your order is ready for delivery!',
    //             ];
    //             $response = Http::withHeaders([
    //                 'Authorization' => 'key=AAAAlI42AYc:APA91bEPodqmrmK6_lrw359Mv4oWmWNCdip8YrSZmgpMWKirR72VumV4svZHRhn3kcgkeAvuwKHc5mdaygTfYc-9KGg1ezwG9YFWa_kNACRvdbNlqBu387DqojPZZOTcPAh1qmlnYrUz',
    //                 'Content-Type' => 'application/json',
    //             ])->post('https://fcm.googleapis.com/fcm/send', [
    //                 'to' => $newFcmToken,
    //                 'notification' => $notificationData,
    //             ]);

    //             if (!$response->successful()) {
    //                 return redirect()->back()->with(['status' => false, 'message' => 'Notification Failed to Send']);
    //             }
    //         }
    //     }
    //     return redirect()->back()->with(['status' => true, 'message' => 'Order Updated Successfully']);
    // }

    public function updateStatus(Request $request, $orderId)
    {
        // 1️⃣ Validate status
        $request->validate([
            'status' => 'required|in:Pending,Order Ready,Delivered',
        ]);

        // 2️⃣ Find order
        $order = Order::find($orderId);
        if (!$order) {
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Order Updated Unsuccessfully'
            ]);
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // 3️⃣ Update order status
        $order->status = $newStatus;

        // 4️⃣ If Delivered, reset seen (website logic untouched)
        if ($newStatus === 'Delivered') {
            $order->seen = 0;
            $reward = Reward::where('user_id', $order->user_id)->first();
            $points = OrderComplationReward::first()->points ?? 0;
            if($reward) {
                $reward->update([
                    'rewards' => $reward->rewards + $points,
                    'redeemed' => 0,
                ]);
            }else { 
                Reward::create([
                    'user_id' => $order->user_id,
                    'rewards' => $points,
                    'redeemed' => 0,
                ]);
            }
        }

        $order->save();

        // 5️⃣ If status changed → APP notification + DB notification
        if ($oldStatus !== $newStatus) {

            $user = $order->user; // relation se user

            if ($user) {

                $title = 'Order Update';
                $description = '';

                if ($newStatus === 'Order Ready') {
                    $description = 'Your order is ready for delivery!';
                } elseif ($newStatus === 'Delivered') {
                    $description = 'Your order has been delivered successfully!';
                }

                // =========================
                // 🔔 SAVE NOTIFICATION IN DB
                // =========================
                \App\Models\Notification::create([
                    'user_id'     => $user->id,
                    'title'       => $title,
                    'description' => $description,
                    'seenByUser'  => 0,
                ]);

                // =========================
                // 🔔 PUSH NOTIFICATION (APP)
                // =========================
                if ($user->fcmtoken) {
                    dispatch(new \App\Jobs\JobNotification(
                        $user->fcmtoken,
                        $title,
                        $description,
                        [
                            'order_id' => $order->id,
                            'status'   => $newStatus
                        ]
                    ));
                }
            }
        }

        return redirect()->back()->with([
            'status' => true,
            'message' => 'Order Updated Successfully'
        ]);
    }


    // public function order(Request $request)
    // {
    //     // return $request;
    //     try {
    //         $user = Auth::guard('user')->user();
    //         $userId = $user->id;
    //         $products = session('cart', []);
    //         $vehicle_color = session('vehicle_color', []);
    //         $vehicle_number = session('vehicle_number', []);
    //         $redeemed = session('redeemed', []);
    //         // return $redeemed;
    //         $dateTime = session('time', []);
    //         $startTime = session('start_time', []);
    //         $tip_amount = session('tip_amount', []);
    //         $orderTotal = session('orderTotal', []);

    //         $total = 0;
    //         foreach ($products as $id => $details) {
    //             $branchId = $details['branch_id'];
    //         }

    //         $client = new SquareClient([
    //             'accessToken' => $this->getAccessToken($branchId),
    //             'environment' => Environment::SANDBOX
    //         ]);
    //         $paymentsApi = $client->getPaymentsApi();
    //         $requestBody = new CreatePaymentRequest(
    //             $request->sourceId,
    //             $request->idempotencyKey
    //         );
    //         $amount_money = new Money();
    //         // $amount_money->setAmount($orderTotal - $request->redeemed * 100);
    //         if (isset($redeemed) && $redeemed <= $orderTotal) {
    //             $amount_money->setAmount(max(0, $orderTotal - $redeemed) * 100);
    //         } else {
    //             $amount_money->setAmount($orderTotal * 100);
    //         }
    //         $amount_money->setCurrency('USD');
    //         $requestBody->setAmountMoney($amount_money);
    //         $response = $paymentsApi->createPayment($requestBody);
    //         if ($response->isSuccess()) {
    //             $order = new Order();
    //             $order->code = random_int(10000000, 99999999);
    //             $order->user_id = $userId;
    //             $order->vehicle_color = $vehicle_color ? $vehicle_color : 'NULL';
    //             $order->vehicle_number = $vehicle_number ?  $vehicle_number : 'NULL';
    //             $order->redeemed = $redeemed ? $redeemed : 'NULL';
    //             $order->status = 'Pending';
    //             $order->payment = 'cash on delivery';
    //             if (isset($dateTime['date'])) {
    //                 $order->date = $dateTime['date'];
    //             } else {
    //                 $order->date = null;
    //             }
    //             if (isset($dateTime['time'])) {
    //                 $order->time = $dateTime['time'];
    //             } else {
    //                 $order->time = $startTime;
    //             }
    //             foreach ($products as $id => $details) {
    //                 $total += floatval($details['price']) * floatval($details['quantity']);
    //             }
    //             $branchId = $details['branch_id'];
    //             $branch = Branch::find($branchId);
    //             if ($branch && $branch->status == 1) {
    //                 $tax = $branch->tax;
    //             }
    //             $order->total_amount = $total;
    //             $order->save();

    //             $orderId = $order->id;
    //             // return  $orderId;
    //             foreach ($products as $id => $details) {
    //                 $orderItem = new OrderItem();
    //                 $orderItem->order_id = $orderId;
    //                 $orderItem->product_id = $details['product_id'];
    //                 $orderItem->product_size = isset($details['size']) ? $details['size'] : 'NULL';
    //                 $orderItem->product_price = $details['price'];
    //                 $orderItem->branch_id = $details['branch_id'];
    //                 $orderItem->product_name = $details['name'];
    //                 $orderItem->quantity = $details['quantity'];
    //                 if (isset($tip_amount) && is_scalar($tip_amount)) {
    //                     $orderItem->tip = $tip_amount;
    //                 } elseif (is_array($tip_amount)) {
    //                     $orderItem->tip = array_sum($tip_amount);
    //                 } else {
    //                     $orderItem->tip = '0';
    //                 }
    //                 $orderItem->sub_total = floatval($details['price']) * floatval($details['quantity']);
    //                 $orderItem->save();
    //                 foreach ($details['toppings_by_category'] as $categoryId => $toppingIds) {
    //                     // return $toppingIds;
    //                     foreach ($toppingIds as $toppingId) {
    //                         $orderItemTopping = new OrderItemToppings();
    //                         $orderItemTopping->order_item_id = $orderItem->id;
    //                         $orderItemTopping->topping_id = $toppingId;
    //                         $orderItemTopping->category_id = $categoryId; // Save category ID
    //                         $orderItemTopping->save();
    //                         $topping = Topping::find($toppingId);
    //                         if ($topping) {
    //                             $total += $topping->price;
    //                         }
    //                     }
    //                 }
    //             };
    //             $order->total_amount = $total + $tip_amount + $branch->tax;
    //             $order->save();

    //             //loyality points code start
    //             $points = $order->total_amount;
    //             if ($user) {

    //                 // Retrieve the existing points of the user
    //                 $existingPoints = $user->point;
    //                 // Add the new points to the existing points
    //                 $totalPoints = $existingPoints + $points;
    //                 // Round down to the nearest integer
    //                 $totalPoints = floor($totalPoints);
    //                 // Update the user's points
    //                 $user->update(['point' => $totalPoints]);
    //             }

    //             $reward = Reward::where('user_id', $user->id)->first();
    //             $redeemedPoints = $redeemed ? $redeemed : '0';
    //             if ($totalPoints >= 150) {
    //                 $rewards = floor(($totalPoints / 150)) * 5;
    //                 if ($reward) {
    //                     $redeemedPoints += $reward->redeemed;
    //                     $reward->update([
    //                         'rewards' => $rewards,
    //                         'redeemed' => $redeemedPoints,
    //                     ]);
    //                 } else {
    //                     Reward::create([
    //                         'user_id' => $user->id,
    //                         'rewards' => $rewards,
    //                         'redeemed' => 0,
    //                     ]);
    //                 }
    //             }
    //             //loyality points code end

    //             $orderCode = $order->code;
    //             Mail::to($user->email)->send(new OrderConfirm($orderCode));
    //             Session::forget(['cart', 'time', 'start_time', 'tip_amount', 'vehicle_color', 'vehicle_number', 'redeemed']);
    //             // return redirect()->route('my-order')->with(['status' => true, 'message' => 'Order Placed Successfully']);
    //             return response()->json($response->getResult());
    //         } else {
    //             $errors = $response->getErrors();
    //             foreach ($errors as $error) {
    //                 if ($error->getCode() === 'PAN_FAILURE') {
    //                     return response()->json(['error' => $error->getDetail()], 400);
    //                 }
    //             }
    //             return response()->json(['error' => 'Payment failed. Please try again later.'], 400);
    //         }
    //     } catch (ApiException $e) {
    //         $errorDetail = $e->getResponseBody()->errors[0]->detail;
    //         return response()->json(['error' => $errorDetail], 400);
    //     }
    // }
    public function order(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::guard('user')->user();
            $userId = $user->id;
            $products = session('cart', []);
            $vehicle_color = session('vehicle_color', []);
            $vehicle_number = session('vehicle_number', []);
            $redeemedAmount = session('redeem_amount', []);
            $redeemedPoints = session('redeem_points', []);
            $dateTime = session('time', []);
            $startTime = session('start_time', []);
            $tip_amount = session('tip_amount', []);
            $orderTotal = session('orderTotal', []);
            $deliveryCharge = session('delivery_charge', 0);
            // return $redeemedAmount;
            $total = 0;
            foreach ($products as $id => $details) {
                $branchId = $details['branch_id'];
            }

            // ✅ CREATE ORDER WITHOUT PAYMENT GATEWAY
            $order = new Order();
            $order->code = random_int(10000000, 99999999);
            $order->user_id = $userId;
            $order->vehicle_color = $vehicle_color ?: 'NULL';
            $order->vehicle_number = $vehicle_number ?: 'NULL';
            $order->redeemed = $redeemedAmount ?: 0;
            $order->redeemed_points = $redeemedPoints ?: 0;
            $order->status = 'Pending';
            $order->payment = 'offline'; // ✅ manual payment
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

            $orderId = $order->id;

            // ✅ Save order items and toppings
            foreach ($products as $id => $details) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $orderId;
                $orderItem->product_id = $details['product_id'];
                $orderItem->product_complementary_id = $details['complementary']['product_id'] ?? null;
                $orderItem->product_size = $details['size'] ?? 'NULL';
                $orderItem->product_price = $details['price'];
                $orderItem->branch_id = $details['branch_id'];
                $orderItem->product_name = $details['name'];
                $orderItem->quantity = $details['quantity'];
                $orderItem->tip = is_array($tip_amount) ? array_sum($tip_amount) : ($tip_amount ?: 0);
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

            $order->total_amount = $total + ($tip_amount ?: 0) + $tax + $deliveryCharge - ($redeemedAmount ?: 0);
            // ✅ Extract delivery info from session cart
            $order->save();

            // ✅ Loyalty points logic
            $points = $order->total_amount;
            if ($user) {
                $existingPoints = $user->point;
                $totalPoints = floor($existingPoints + $points);
                $user->update(['point' => $totalPoints]);
            }

            $reward = Reward::where('user_id', $user->id)->first();
            if($reward) {
               $reward->update([
                    'rewards' =>$reward->rewards - $redeemedPoints ,
                    'redeemed' => $redeemedPoints + ($reward->redeemed ?? 0),
                ]);
            }

            // ✅ Email notification
            $orderCode = $order->code;
            // Mail::to($user->email)->send(new OrderConfirm($orderCode));

            // ✅ Clear session
            session()->forget('cart');
            session()->forget('tip_amount');
            session()->forget('redeem_points');
            session()->forget('redeem_amount');
            session()->forget('vehicle_color');
            session()->forget('vehicle_number');
            session()->forget('time');
            session()->forget('start_time'); 
            session()->forget('delivery_charge');
            DB::commit();
            return redirect()->route('my-order')->with(['status' => true, 'message' => 'Order placed successfully! Payment will be handled manually.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function stripePayment(Request $request)
{
    $products = session('cart', []);
    $total = 0;

    foreach ($products as $details) {
        $total += $details['price'] * $details['quantity'];
    }

    Stripe::setApiKey(config('services.stripe.secret'));

    $session = StripeSession::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Order Payment',
                ],
                'unit_amount' => $total * 100, // cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => route('stripe.success'),
        'cancel_url' => route('stripe.cancel'),
    ]);

    return redirect($session->url);
}

public function stripeSuccess()
{
     DB::beginTransaction();
        try {
            $user = Auth::guard('user')->user();
            $userId = $user->id;
            $products = session('cart', []);
            $vehicle_color = session('vehicle_color', []);
            $vehicle_number = session('vehicle_number', []);
            $redeemedAmount = session('redeem_amount', []);
            $redeemedPoints = session('redeem_points', []);
            $dateTime = session('time', []);
            $startTime = session('start_time', []);
            $tip_amount = session('tip_amount', []);
            $orderTotal = session('orderTotal', []);
            // return $redeemedAmount;
            $total = 0;
            foreach ($products as $id => $details) {
                $branchId = $details['branch_id'];
            }

            // ✅ CREATE ORDER WITHOUT PAYMENT GATEWAY
            $order = new Order();
            $order->code = random_int(10000000, 99999999);
            $order->user_id = $userId;
            $order->vehicle_color = $vehicle_color ?: 'NULL';
            $order->vehicle_number = $vehicle_number ?: 'NULL';
            $order->redeemed = $redeemedAmount ?: 'NULL';
            $order->redeemed_points = $redeemedPoints ?: 'NULL';
            $order->status = 'Pending';
            $order->payment = 'offline'; // ✅ manual payment
            $order->date = $dateTime['date'] ?? null;
            $order->time = $dateTime['time'] ?? $startTime;

            foreach ($products as $id => $details) {
                $total += floatval($details['price']) * floatval($details['quantity']);
            }

            $branch = Branch::find($branchId);
            $tax = $branch && $branch->status == 1 ? $branch->tax : 0;
            $order->total_amount = $total;
            $order->save();

            $orderId = $order->id;

            // ✅ Save order items and toppings
            foreach ($products as $id => $details) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $orderId;
                $orderItem->product_id = $details['product_id'];
                $orderItem->product_complementary_id = $details['complementary']['product_id'] ?? null;
                $orderItem->product_size = $details['size'] ?? 'NULL';
                $orderItem->product_price = $details['price'];
                $orderItem->branch_id = $details['branch_id'];
                $orderItem->product_name = $details['name'];
                $orderItem->quantity = $details['quantity'];
                $orderItem->tip = is_array($tip_amount) ? array_sum($tip_amount) : ($tip_amount ?: 0);
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

            $order->total_amount = $total + ($tip_amount ?: 0) + $tax - ($redeemedAmount ?: 0);
            // ✅ Extract delivery info from session cart
            $order->save();

            // ✅ Loyalty points logic
            $points = $order->total_amount;
            if ($user) {
                $existingPoints = $user->point;
                $totalPoints = floor($existingPoints + $points);
                $user->update(['point' => $totalPoints]);
            }

            $reward = Reward::where('user_id', $user->id)->first();
            if($reward) {
               $reward->update([
                    'rewards' =>$reward->rewards - $redeemedPoints ,
                    'redeemed' => $redeemedPoints + ($reward->redeemed ?? 0),
                ]);
            }

            // ✅ Email notification
            $orderCode = $order->code;
            // Mail::to($user->email)->send(new OrderConfirm($orderCode));

            // ✅ Clear session
            session()->forget('cart');
            session()->forget('tip_amount');
            session()->forget('redeem_points');
            session()->forget('redeem_amount');
            session()->forget('vehicle_color');
            session()->forget('vehicle_number');
            session()->forget('time');
            session()->forget('start_time'); 
            DB::commit();
            return redirect()->route('my-order')->with(['status' => true, 'message' => 'Order placed successfully! Payment will be handled manually.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }

    $order->payment = 'stripe'; // ✅ mark paid

    return redirect()->route('my-order')
        ->with(['status' => true, 'message' => 'Payment successful & order placed!']);
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
}
