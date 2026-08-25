<?php

namespace App\Http\Controllers\Home;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Topping;
use App\Models\Category;
use App\Models\Reward;
use App\Models\RewardSetting;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use App\Models\UserTimeSlotes;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

class CartController extends Controller
{
    public function myCart()
    {
        $carts = session('cart');
        // return $carts;
        $branchess = Branch::all();
        $userId = Auth::guard('user')->id();
        $userTimeSlots = UserTimeSlotes::where('user_id', $userId)
            ->first();
        $timeSlots = TimeSlot::all();
        // return $carts;
        $addingToExisting = false;
        try {
            $addingToExisting = app(\App\Services\OrderLifecycleService::class)
                ->hasActiveAddToOrderSession($userId ? (int) $userId : null);
        } catch (\Throwable $e) {
            $addingToExisting = false;
        }
        $loyaltyPoints = Reward::where('user_id', $userId)->first();
        $pricePerPoint = RewardSetting::first();
        // return $carts;
        $lastItem = collect($carts)->last();
        $distanceData = $this->applyDistanceCalculation($carts);
        // return $loyaltyPoints;
        return view('home.my-cart', compact('timeSlots', 'userTimeSlots', 'branchess','loyaltyPoints', 'carts','pricePerPoint','distanceData', 'addingToExisting'));
    }
    Private function applyDistanceCalculation($cart)
    {
        $lastItem = collect($cart)->last();
        $deliveryStatus = $lastItem['delivery_status'] ?? null;
        $userLat = $lastItem['home_address_latitude'] ?? null;
        $userLng = $lastItem['home_address_longitude'] ?? null;
        // Branch location
        $branchLat = Branch::first()->latitude ?? 0;
        $branchLng = Branch::first()->longitude ?? 0;
        $deliveryCharge = 0;
        $distance = 0;
                if ($deliveryStatus == 2 && $userLat && $userLng) {
                    $distance = $this->getDistanceFromGoogle($branchLat, $branchLng, $userLat, $userLng);
                    // return $distance;
                    if ($distance <= 1) {
                        $deliveryCharge = 1.99;
                    } elseif ($distance <= 2) {
                        $deliveryCharge = 2.99;
                    } elseif ($distance <= 3) {
                        $deliveryCharge = 3.49;
                    } elseif ($distance <= 5) {
                        $deliveryCharge = 4.99;
                    } else {
                        $deliveryCharge = 5.99;
                    }
                }
                // session save
                Session::put('delivery_charge', $deliveryCharge);
                Session::put('distance', $distance);
                return [
                    'delivery_charge' => $deliveryCharge,
                    'distance_miles' => round($distance, 2),
                ];
    }
    Private function getDistanceFromGoogle($originLat, $originLng, $destLat, $destLng)
        {
            $apiKey = env('GOOGLE_MAPS_API_KEY');

            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => $originLat . ',' . $originLng,
                'destinations' => $destLat . ',' . $destLng,
                'units' => 'imperial', // miles
                'key' => $apiKey
            ]);

            $data = $response->json();

            if (
                isset($data['rows'][0]['elements'][0]['distance']['value'])
            ) {
                // meters → miles
                $meters = $data['rows'][0]['elements'][0]['distance']['value'];
                $miles = $meters * 0.000621371;

                return $miles;
            }

            return 0;
        }
public function addToCart(Request $request)
{
    try {
        $product = Product::with(['variants', 'menu'])->findOrFail($request->product_id);
        $complementryProduct = $request->filled('complementary_id')
            ? Product::where('id', $request->complementary_id)->first()
            : null;

        if ($request->branch_id) {
            $branch = Branch::where('id', $request->branch_id)->first();
            if (!$branch) return response()->json(['success' => false, 'message' => 'Branch not found.'], 404);
        } else {
            $branch = Branch::where('status', 1)->first();
            if (!$branch) return response()->json(['success' => false, 'message' => 'No active branch.'], 404);
        }

        $toppingsByCategory = $request->toppings_by_category ?? [];
        $cart = Session::get('cart', []);

        $size = '';
        if (!$request->variant_id) {
            $price = floatval(trim($product->price));
            if ($price <= 0 && $product->variants && $product->variants->count() > 0) {
                $variant = $product->variants->first(function ($v) {
                    return (float) $v->price > 0;
                }) ?: $product->variants->first();
                if ($variant) {
                    $price = floatval($variant->price);
                    $size = $variant->size;
                }
            }
            if ($price <= 0) {
                $price = $product->resolvedDisplayPrice();
            }
        } else {
            $variant = $product->variants->where('id', $request->variant_id)->first();
            $price = $variant ? floatval(trim($variant->price)) : 0;
            $size = $variant->size ?? '';
            if ($price <= 0 && $variant) {
                $price = floatval($variant->original_price ?? 0);
            }
        }

        $cartKey = $request->product_id . '-' . ($request->variant_id ?? '');

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += (int)$request->quantity;
        } else {
            // ✅ Initialize cart item — NO premature session save, NO undefined variables
            $cart[$cartKey] = [
                "product_id"                  => $product->id,
                "variant_id"                  => (int)$request->variant_id,
                "name"                        => $product->name,
                "price"                       => $price,
                "size"                        => $size,
                "image"                       => $product->image,
                "branch_id"                   => $branch->id,
                "branch_name"                 => $branch->name,
                "quantity"                    => (int)$request->quantity,
                "delivery_status"             => $request->delivery_status ?? 1,
                "delivery_address"            => $request->delivery_address ?? '',
                "location"                    => $request->location ?? '',
                "toppings_by_category"        => [],   // ✅ filled below
                "toppingsName_by_categoryName" => [],  // ✅ filled below (was undefined!)
                "home_address_latitude"       => $request->lat,
                "home_address_longitude"      => $request->lng,
                "complementary"               => null,
                "fulfillment"                 => null,
            ];

            // ✅ Fill toppings BEFORE session save
            foreach ($toppingsByCategory as $toppingCategory) {
                $categoryId  = $toppingCategory['category_id'];
                $toppingIds  = $toppingCategory['toppings'];

                $existing = $cart[$cartKey]['toppings_by_category'][$categoryId] ?? [];
                $cart[$cartKey]['toppings_by_category'][$categoryId] = array_unique(array_merge($existing, $toppingIds));
            }

            foreach ($toppingsByCategory as $toppingCategory) {
                $categoryId   = $toppingCategory['category_id'];
                $toppingIds   = $toppingCategory['toppings'];
                $categoryName = Category::findOrFail($categoryId)->name;
                $toppingNames = Topping::whereIn('id', $toppingIds)->pluck('name')->toArray();

                $cart[$cartKey]['toppingsName_by_categoryName'][] = [
                    'category_name' => $categoryName,
                    'topping_names' => $toppingNames,
                ];
            }
        }

        // ✅ Handle complementary
        $cart[$cartKey]['complementary'] = $complementryProduct ? [
            'id'    => $complementryProduct->id,
            'name'  => $complementryProduct->name,
            'image' => $complementryProduct->image,
            'price' => 0,
        ] : null;

        // ✅ Save ONCE at the end
        Session::put('cart', $cart);

        $fromWholesalePage = $request->boolean('wholesale') || (string) $request->input('wholesale') === '1';
        $isWholesale = $fromWholesalePage;
        if ($isWholesale && !session('wholesale_delivery_date')) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a wholesale delivery date (Monday, Thursday or Saturday, 7:00 PM – 10:00 PM) before adding items.',
            ], 422);
        }
        if ($isWholesale) {
            Session::put('selected_order_type', 'wholesale');
            $cart[$cartKey]['fulfillment'] = 'wholesale';
            $cart[$cartKey]['delivery_status'] = 2;
            Session::put('cart', $cart);
        } elseif (session('selected_order_type') === 'wholesale') {
            Session::put('selected_order_type', 'standard');
            Session::forget('wholesale_delivery_date');
        }
        if (empty($cart[$cartKey]['fulfillment'])) {
            $cart[$cartKey]['fulfillment'] = ((int) ($cart[$cartKey]['delivery_status'] ?? 1) === 2)
                ? 'home_delivery'
                : 'takeaway';
            Session::put('cart', $cart);
        }
        // #region agent log
        file_put_contents(base_path('debug-1796d5.log'), json_encode([
            'sessionId' => '1796d5',
            'hypothesisId' => 'W2',
            'location' => 'CartController.php:addToCart',
            'message' => 'addToCart wholesale vs standard',
            'data' => [
                'product_id' => $product->id,
                'menu_id' => $product->menu_id ?? null,
                'menu_type' => optional($product->menu)->type,
                'menu_slug' => optional($product->menu)->slug,
                'fromWholesalePage' => $fromWholesalePage,
                'isWholesale' => $isWholesale,
                'session_order_type' => session('selected_order_type'),
                'resolved_price' => $price,
                'product_price' => (float) ($product->price ?? 0),
                'variant_count' => $product->variants ? $product->variants->count() : 0,
                'fulfillment' => $cart[$cartKey]['fulfillment'] ?? null,
            ],
            'timestamp' => (int) round(microtime(true) * 1000),
        ]) . "\n", FILE_APPEND);
        // #endregion

        $addingToOrderId = Session::get('adding_to_order_id');
        if ($addingToOrderId && Auth::guard('user')->check()) {
            $order = \App\Models\Order::where('id', $addingToOrderId)
                ->where('user_id', Auth::guard('user')->id())
                ->first();
            $lifecycle = app(\App\Services\OrderLifecycleService::class);
            if ($order && $lifecycle->canModify($order)) {
                try {
                    $lifecycle->addSessionCartToOrder($order, $cart, ['source' => 'web-add']);
                } catch (\Throwable $e) {
                    Session::forget('adding_to_order_id');
                    return response()->json([
                        'success' => true,
                        'data' => count($cart),
                        'cart' => $cart,
                        'message' => 'Item kept in cart. Place a new order when you are ready.',
                    ]);
                }
                Session::forget('cart');
                return response()->json([
                    'success' => true,
                    'receipt_generated' => true,
                    'message' => 'Your product has been added in My Orders.',
                    'data' => 0,
                    'cart' => [],
                ]);
            }
            Session::forget('adding_to_order_id');
        }

        return response()->json([
            'success' => true,
            'data'    => count($cart),
            'cart'    => $cart,
        ]);

    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

    public function remove(Request $request)
    {
        if ($request->product_id) {
            $cart = session()->get('cart');

            if ($request->variant_id) {
                // Remove the product with the specific variant ID
                $key = $request->product_id . '-' . $request->variant_id;
                unset($cart[$key]);
            } else {
                // Remove the product without considering the variant
                foreach ($cart as $key => $item) {
                    if ($item['product_id'] == $request->product_id) {
                        unset($cart[$key]);
                    }
                }
            }

            session()->put('cart', $cart);

            $data = count((array) $cart);

            return response()->json([
                'success' => true,
                'message' => 'Product removed from the cart successfully!',
                'cart' => $cart,
                'data' => $data,
            ]);
        }
    }


    public function updateCart(Request $request)
    {
        $cart = session()->get('cart', []);
        // Validate input parameters
        if ($request->has(['quantity', 'product_id'])) {
            // Determine the key for the product in the cart
            if ($request->has('variant_id')) {
                $key = $request->product_id . '-' . $request->variant_id;
            }

            // Check if product exists in the cart
            if (isset($cart[$key])) {
                // Update the quantity
                $cart[$key]['quantity'] = $request->quantity;

                // Update session with modified cart
                session()->put('cart', $cart);

                // Get updated cart data
                $cart = session('cart', []);
                $data = count($cart);

                // Build response with updated cart and existing product details
                return response()->json([
                    'success' => true,
                    'message' => 'Quantity updated in the cart successfully!',
                    'cart' => $cart, // Contains updated quantity along with existing details
                    'data' => $data,
                    'product' => [ // Include details of the updated product
                        'product_id' => $request->product_id,
                        'name' => $cart[$key]['name'] ?? null,
                        'price' => $cart[$key]['price'] ?? null,
                        'image' => $cart[$key]['image'] ?? null,
                        'size' => $cart[$key]['size'] ?? null,
                    ],
                ]);
            } elseif ($request->has(['quantity', 'product_id'])) {
                $cart = session()->get('cart', []);
                // Check if product exists in the cart
                if (isset($cart[$request->product_id . '-'])) {
                    // Update the quantity only
                    $cart[$request->product_id . '-']['quantity'] = $request->quantity;

                    // Update session with modified cart
                    session()->put('cart', $cart);

                    // Get updated cart data
                    $cart = session('cart', []);
                    $data = count($cart);

                    // Build response with updated cart and existing product details
                    return response()->json([
                        'success' => true,
                        'message' => 'Quantity updated in the cart successfully!',
                        'cart' => $cart, // Contains updated quantity along with existing details
                        'data' => $data,
                        'product' => [ // Include details of the updated product
                            'product_id' => $request->product_id,
                            'name' => $cart[$request->product_id]['name'] ?? null, // Access name from existing cart data
                            'price' => $cart[$request->product_id]['price'] ?? null, // Access price from existing cart data
                            'image' => $cart[$request->product_id]['image'] ?? null, // Access image from existing cart data
                        ],
                    ]);
                } else {
                    // Product not found in the cart
                    return response()->json([
                        'success' => false,
                        'message' => 'Product with ID ' . $request->product_id . ' and variant ID ' . ($request->variant_id ?? 'N/A') . ' not found in your cart.',
                    ]);
                }
            } else {
                // Invalid request, handle missing inputs
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request. Please provide quantity and product_id.',
                ]);
            }
        }
    }
    public function updateMyCartValue(Request $request)
    {
        // Load existing cart data from session
        $cart = session()->get('cart', []);

        // Validate input parameters
        if ($request->has(['quantity', 'product_id'])) {
            // Determine the key for the product in the cart
            if ($request->has('variant_id')) {
                $key = $request->product_id . '-' . $request->variant_id;
            }
            // else {
            //     $key = $request->product_id;
            // }

            // Check if product exists in the cart
            if (isset($cart[$key])) {
                // Update the quantity
                $cart[$key]['quantity'] = $request->quantity;

                // Update session with modified cart
                session()->put('cart', $cart);

                // Get updated cart data
                $cart = session('cart', []);
                $data = count($cart);

                // Build response with updated cart and existing product details
                return response()->json([
                    'success' => true,
                    'message' => 'Quantity updated in the cart successfully!',
                    'cart' => $cart, // Contains updated quantity along with existing details
                    'data' => $data,
                    'product' => [ // Include details of the updated product
                        'product_id' => $request->product_id,
                        'name' => $cart[$key]['name'] ?? null,
                        'price' => $cart[$key]['price'] ?? null,
                        'image' => $cart[$key]['image'] ?? null,
                        'size' => $cart[$key]['size'] ?? null,
                    ],
                ]);
            } elseif ($request->has(['quantity', 'product_id'])) {
                $cart = session()->get('cart', []);

                // Check if product exists in the cart
                if (isset($cart[$request->product_id . '-'])) {
                    // Update the quantity only
                    $cart[$request->product_id . '-']['quantity'] = $request->quantity;
                    // Update session with modified cart
                    session()->put('cart', $cart);
                    // Get updated cart data
                    $cart = session('cart', []);
                    $data = count($cart);
                    // Build response with updated cart and existing product details
                    return response()->json([
                        'success' => true,
                        'message' => 'Quantity updated in the cart successfully!',
                        'cart' => $cart, // Contains updated quantity along with existing details
                        'data' => $data,
                        'product' => [ // Include details of the updated product
                            'product_id' => $request->product_id,
                            'name' => $cart[$request->product_id]['name'] ?? null, // Access name from existing cart data
                            'price' => $cart[$request->product_id]['price'] ?? null, // Access price from existing cart data
                            'image' => $cart[$request->product_id]['image'] ?? null, // Access image from existing cart data
                        ],
                    ]);
                } else {
                    // Product not found in the cart
                    return response()->json([
                        'success' => false,
                        'message' => 'Product with ID ' . $request->product_id . ' and variant ID ' . ($request->variant_id ?? 'N/A') . ' not found in your cart.',
                    ]);
                }
            } else {
                // Invalid request, handle missing inputs
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request. Please provide quantity and product_id.',
                ]);
            }
        }
    }
    public function updateTime(Request $request)
    {
        $userId = Auth::guard('user')->id();
        $date = $request->date_input;
        $time = $request->input('time-radio');
        $userTimeSlot = UserTimeSlotes::where('user_id', $userId)
            ->first();
        if ($userId) {
            if ($userTimeSlot) {
                // If the user already has a time slot, update the existing one
                $userTimeSlot->time = $time;
                $userTimeSlot->date = $date;
                $userTimeSlot->save();
            } else {
                // If the user doesn't have a time slot, create a new one
                UserTimeSlotes::create([
                    'user_id' => $userId,
                    'date' => $date, // Include the date in the creation if needed
                    'time' => $time,
                ]);
            }
        }
        $newArray = [
            'date' => $date,
            'time' => $time,
        ];

        session(['time' => $newArray]);
    }

    public function timeSlotes(Request $request)
    {
        $start_time = $request->selectedTime;
        session(['start_time' => $start_time]);
        return response()->json(['success' => true]);
    }

    public function storeTipInSession(Request $request)
    {
        $tipAmount = $request->input('tipAmount');
        $redeemAmount = $request->input('redeemAmount');
        $redeemPoints = $request->input('redeemPoints');
        if (is_array($tipAmount)) {
            $tipAmount = array_sum($tipAmount);
        }
        if (is_array($redeemAmount)) {
            $redeemAmount = array_sum($redeemAmount);
        }
        session(['tip_amount' => $tipAmount]);
        session(['redeem_amount' => $redeemAmount]);
        session(['redeem_points' => $redeemPoints]);
        return response()->json(['success' => true]);
    }

    public function storeVehicleInfo(Request $request)
    {
        $vehicleColor = $request->input('vehicle_color');
        $vehicleNumber = $request->input('vehicle_number');
        $redeemed = $request->input('redeemed');
        if ($vehicleColor && $vehicleNumber) {
            session(['vehicle_color' => $vehicleColor]);
            session(['vehicle_number' => $vehicleNumber]);
        }
        if ($redeemed) {
            session(['redeemed' => trim($redeemed)]);
        }
    }

	
// CartController.php mein naya method add karein
public function storeTipAndDelivery(Request $request)
{
    // Tip aur delivery dono ko session mein save karein
    session([
        'tip' => $request->tipAmount,
        'delivery_charges' => $request->deliveryAmount,
    ]);
    
    \Log::info('Tip and Delivery saved to session:', [
        'tip' => $request->tipAmount,
        'delivery' => $request->deliveryAmount
    ]);
    
    return response()->json(['success' => true, 'message' => 'Tip and delivery saved']);
}

    public function prepareCheckout(Request $request)
    {
        $keys = $request->input('cart_keys', []);
        if (!is_array($keys)) {
            $keys = [];
        }
        $cart = session('cart', []);
        $valid = [];
        foreach ($keys as $key) {
            if (isset($cart[$key])) {
                $valid[] = (string) $key;
            }
        }
        // #region agent log
        file_put_contents(base_path('debug-1796d5.log'), json_encode([
            'sessionId' => '1796d5',
            'runId' => 'post-fix',
            'hypothesisId' => 'H5',
            'location' => 'CartController.php:prepareCheckout',
            'message' => 'selected cart keys for checkout',
            'data' => [
                'posted_count' => count($keys),
                'valid_count' => count($valid),
                'cart_count' => is_array($cart) ? count($cart) : 0,
            ],
            'timestamp' => (int) round(microtime(true) * 1000),
        ]) . "\n", FILE_APPEND);
        // #endregion
        if ($valid === []) {
            return redirect()->route('my-cart')->with([
                'status' => false,
                'message' => 'Please select at least one item to place the order.',
            ]);
        }
        Session::put('checkout_keys', $valid);
        return redirect()->route('checkout');
    }
}
