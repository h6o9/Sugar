<?php

namespace App\Http\Controllers\Api;

use Log;
use Exception;
use App\Models\Order;
use App\Models\Topping;
use App\Services\OrderLifecycleService;
use App\Services\WholesaleScheduleService;
use App\Support\AppCartContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\AddToCartItem;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\AddToCartItemTopping;
use Illuminate\Support\Facades\Auth;

class AddToCartController extends Controller
{
    //

public function addToCart(Request $request)
{
    DB::beginTransaction();

    try {

        $userId = auth()->id();

        $channelGate = $this->applyStorefrontChannel($request, (int) $userId);
        if ($channelGate !== null) {
            return $channelGate;
        }

        // =========================
        // BASIC INPUTS
        // =========================
        $productId   = (int) $request->product_id;
        $productName = $request->product_name;
        $branchId    = (int) $request->branch_id;
        $quantity    = (int) ($request->quantity ?? 1);
        $variantId   = $request->variant_id ? (int) $request->variant_id : null;

        // ✅ NEW: complementary product
        $complementaryId = $request->product_complementary_id ?? null;

        // =========================
        // FRONTEND PRICES
        // =========================
        $price = (float) $request->price;
        $originalPrice = (float) $request->original_price;

        // =========================
        // GET TIPS
        // =========================
        $tips = AddToCartItem::where('user_id', $userId)
            ->where('tips', '>', 0)
            ->value('tips') ?? 0;

        // =========================
        // GET TOPPINGS
        // =========================
        $toppingDetails = [];
        if ($request->toppings) {
            $toppingsJson = json_decode($request->toppings, true);

            if (!empty($toppingsJson) && is_array($toppingsJson)) {
                foreach ($toppingsJson as $categoryKey => $toppingsArray) {
                    foreach ($toppingsArray as $item) {
                        if (isset($item['topping_id']) && isset($item['category_id'])) {
                            $toppingDetails[] = [
                                'topping_id'  => (int) $item['topping_id'],
                                'category_id' => (int) $item['category_id'],
                            ];
                        }
                    }
                }
            }
        }

        // =========================
        // CHECK EXISTING CART ITEM
        // =========================
        $existingItems = AddToCartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->get();

        $existingCartItem = null;

        foreach ($existingItems as $item) {

            $existingToppings = AddToCartItemTopping::where('add_to_cart_item_id', $item->id)
                ->pluck('topping_id')
                ->sort()
                ->values()
                ->toArray();

            $currentToppings = collect($toppingDetails)
                ->pluck('topping_id')
                ->sort()
                ->values()
                ->toArray();

            if ($existingToppings === $currentToppings) {
                $existingCartItem = $item;
                break;
            }
        }

        // =========================
        // EXISTING ITEM UPDATE
        // =========================
        if ($existingCartItem) {

            $existingCartItem->quantity += $quantity;
            $existingCartItem->price += $price;
            $existingCartItem->original_price += $originalPrice;

            $existingCartItem->subtotal = $existingCartItem->price;
            $existingCartItem->estimated_total = $existingCartItem->price + $tips;

            // ✅ FIX: complementary update bhi
            $existingCartItem->product_complementary_id = $complementaryId;

            $existingCartItem->tips = $tips;
            $existingCartItem->save();

            $cartItemId = $existingCartItem->id;

        } else {

            // =========================
            // NEW CART ITEM
            // =========================
            $cartItem = AddToCartItem::create([
                'user_id'          => $userId,
                'product_id'       => $productId,
                'product_name'     => $productName,
                'branch_id'        => $branchId,
                'quantity'         => $quantity,
                'price'            => $price,
                'subtotal'         => $price,
                'tax_amount'       => 0,
                'estimated_total'  => $price + $tips,
                'order_type'       => $request->order_type,
                'delivery_address' => $request->delivery_address,
                'pickup_time'      => $request->pickup_time,
                'tips'             => $tips,
                'original_price'   => $originalPrice,
                'variant_id'       => $variantId,

                // ✅ IMPORTANT FIX
                'product_complementary_id' => $complementaryId,
            ]);

            $cartItemId = $cartItem->id;

            $toppingsInsert = [];

            foreach ($toppingDetails as $topping) {
                $toppingsInsert[] = [
                    'add_to_cart_item_id' => $cartItemId,
                    'topping_id'          => $topping['topping_id'],
                    'category_id'         => $topping['category_id'],
                ];
            }

            if (!empty($toppingsInsert)) {
                AddToCartItemTopping::insert($toppingsInsert);
            }
        }

        // =========================
        // FINAL UPDATE (ONLY CURRENT ITEM)
        // =========================
        $cartItemToUpdate = AddToCartItem::find($cartItemId);

        $cartItemToUpdate->subtotal = $cartItemToUpdate->price;
        $cartItemToUpdate->estimated_total = $cartItemToUpdate->price + $tips;

        $cartItemToUpdate->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'cart_item_id' => $cartItemId,
            'context' => AppCartContext::get((int) $userId),
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        
        // ✅ Show complete validation errors
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
            'error_details' => $e->getMessage()
        ], 422);
        
    } catch (\Illuminate\Database\QueryException $e) {
        DB::rollBack();
        
        // ✅ Show database errors
        return response()->json([
            'success' => false,
            'message' => 'Database error',
            'error' => $e->getMessage(),
            'sql' => $e->getSql() // if available
        ], 500);
        
    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Add to cart error', [
            'error' => $e->getMessage(),
            'request' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);

        // ✅ Show complete error with details
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong while adding to cart.',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString() // for debugging
        ], 500);
    }
}
// cotinue to  payments method

public function proceedToPayment(Request $request)
{
    try {
        $userId = auth()->user()->id;

        $cartItems = AddToCartItem::where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No items in cart to proceed to payment.'
            ], 400);
        }

        // Validate request
        $request->validate([
            'tip' => 'nullable|numeric|min:0',
            'points_to_redeem' => 'nullable|integer|min:0',
        ]);

        $tips = $request->tip ?? 0;
        $branchId = $request->branch_id;
        $pointsToRedeem = $request->points_to_redeem ?? 0;
        
        // =========================
        // GET BRANCH TAX
        // =========================
        $branch = DB::table('branches')
            ->orderBy('id')
            ->first();        
        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Branch not found.'
            ], 404);
        }
        
        $branchTax = $branch->tax ?? 0;
        
        // =========================
        // CALCULATIONS
        // =========================
        $subtotal = $cartItems->sum('price');
        $originalPriceSum = $cartItems->sum('original_price');
        
        // Calculate tax
        $taxAmount = ($subtotal * $branchTax) / 100;
        
        $deliveryCharges = 1.5;
        
        $estimatedTotal = $subtotal + $taxAmount + $tips + $deliveryCharges;
        $originalEstimatedAmount = $originalPriceSum + $taxAmount + $tips + $deliveryCharges;
        $ctx = AppCartContext::get((int) $userId);
        $channel = AppCartContext::normalizeChannel($request->input('channel', $ctx['channel'] ?? 'regular'));
        $driveInDiscount = 0;
        if ($channel === 'drive_in') {
            $drivePercent = app(OrderLifecycleService::class)->driveInPercent();
            $driveInDiscount = round($subtotal * ($drivePercent / 100), 2);
            $estimatedTotal = max(0, $estimatedTotal - $driveInDiscount);
            $originalEstimatedAmount = max(0, $originalEstimatedAmount - $driveInDiscount);
        }
        
        // =========================
        // POINTS REDEMPTION LOGIC (ONLY IF points_to_redeem > 0)
        // =========================
        $pointsDiscount = 0;
        $finalTotal = $estimatedTotal;
        
        if ($pointsToRedeem > 0) {
            // Check if user has enough points
            $userRewards = DB::table('rewards')
                ->where('user_id', $userId)
                ->first();
            
            if (!$userRewards || (int) $userRewards->rewards < $pointsToRedeem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient points. You have ' . ($userRewards->rewards ?? 0) . ' points available.'
                ], 400);
            }
            
            // Get points price from reward_settings
            $rewardSetting = DB::table('reward_settings')
                ->orderBy('id', 'desc')
                ->first();
            
            if (!$rewardSetting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reward settings not configured.'
                ], 400);
            }
            
            // Calculate discount
            $pointsDiscount = $pointsToRedeem * (float) $rewardSetting->price;
            
            // Check if discount exceeds estimated total
            if ($pointsDiscount > $estimatedTotal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Points discount cannot exceed order total. Maximum discount allowed: ' . number_format($estimatedTotal, 2)
                ], 400);
            }
            
            // Apply discount
            $finalTotal = $estimatedTotal - $pointsDiscount;
            
            // ✅ FIXED: Calculate remaining points
            $remainingRewards = (int) $userRewards->rewards - $pointsToRedeem;
            
            // ✅ UPDATE rewards table - MINUS POINTS
            DB::table('rewards')
                ->where('user_id', $userId)
                ->update([
                    'rewards' => $remainingRewards,
                    'updated_at' => now()
                ]);
        }
        
        // =========================
        // STORE CALCULATIONS IN SESSION
        // =========================
        $paymentData = [
            'branch_id' => $branchId,
            'branch_tax' => $branchTax,
            'tips' => $tips,
            'delivery_charges' => $deliveryCharges,
            'subtotal' => $subtotal,
            'original_price_sum' => $originalPriceSum,
            'tax_amount' => $taxAmount,
            'estimated_total' => $estimatedTotal,
            'points_to_redeem' => $pointsToRedeem,
            'points_discount' => $pointsDiscount,
            'final_total' => $finalTotal,
            'calculated_at' => now()
        ];
        
        session(['payment_data_' . $userId => $paymentData]);
        
        // =========================
        // UPDATE CART ITEMS
        // =========================
        foreach ($cartItems as $item) {
            $item->tips = $tips;
            $item->branch_id = $branchId;
            $item->tax_amount = $taxAmount;
            $item->subtotal = $subtotal;
            $item->estimated_total = $finalTotal;
            $item->save();
        }
        
        // =========================
        // PREPARE RESPONSE (EXACT SAME STRUCTURE)
        // =========================
        $responseData = [
            'success' => true,
            'message' => 'Continue to payment',
            'summary' => [
                'subtotal' => round($subtotal, 2),
                'original_price_sum' => round($originalPriceSum, 2),
                'tax_amount' => round($taxAmount, 2),
                'delivery_charges' => round($deliveryCharges, 2),
                'tips' => round($tips, 2),
                'drive_in_discount' => round($driveInDiscount, 2),
                'channel' => $channel,
                'estimated_total' => round($finalTotal, 2),
                'original_estimated_amount' => round($originalEstimatedAmount, 2),
            ]
        ];
        
        // ✅ ONLY ADD POINTS FIELDS IF POINTS WERE REDEEMED
        if ($pointsToRedeem > 0) {
            $responseData['summary']['points_redeemed'] = $pointsToRedeem;
            $responseData['summary']['points_discount'] = round($pointsDiscount, 2);
            $responseData['summary']['estimated_total_before_discount'] = round($estimatedTotal, 2);
            $responseData['summary']['savings'] = round($originalEstimatedAmount - $finalTotal, 2);
        }
        
        return response()->json($responseData);
        
    } catch (\Exception $e) {
        \Log::error('Proceed to payment error', [
            'error' => $e->getMessage(),
            'user_id' => auth()->user()->id ?? null
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ], 500);
    }
}


public function getUserCartItems(Request $request)
{
    $userId = auth()->id();

    $deliveryCharges = (float) $request->input('delivery_charges', 0);
    $tip = (float) $request->input('tip', 0);

    $cartItems = AddToCartItem::with([
        'product:id,name,image'
    ])
    ->where('user_id', $userId)
    ->get();

    if ($cartItems->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No items found in your cart.'
        ], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | PROCESS EACH CART ITEM - CHECK VARIANT FIRST
    |--------------------------------------------------------------------------
    */
    foreach ($cartItems as $item) {
        $basePrice = 0;
        $baseOriginalPrice = 0;
        $variantSize = null;
        
        // Check if variant_id exists in cart item
        if ($item->variant_id && $item->variant_id > 0) {
            // Fetch price from product_variants table
            $variant = DB::table('product_variants')
                ->where('id', $item->variant_id)
                ->select('price', 'original_price', 'size')
                ->first();
            
            if ($variant) {
                $basePrice = (float) $variant->price;
                $baseOriginalPrice = (float) ($variant->original_price ?? $variant->price);
                $variantSize = $variant->size;
            } else {
                // Fallback to product if variant not found
                $product = DB::table('products')
                    ->where('id', $item->product_id)
                    ->select('price', 'original_price')
                    ->first();
                
                if ($product) {
                    $basePrice = (float) $product->price;
                    $baseOriginalPrice = (float) ($product->original_price ?? $product->price);
                }
            }
        } else {
            // No variant, fetch price from products table
            $product = DB::table('products')
                ->where('id', $item->product_id)
                ->select('price', 'original_price')
                ->first();
            
            if ($product) {
                $basePrice = (float) $product->price;
                $baseOriginalPrice = (float) ($product->original_price ?? $product->price);
            }
        }
        
        // Get all toppings for this cart item with their prices
        $toppingsData = DB::table('add_to_cart_item_toppings as act')
            ->leftJoin('toppings as t', 'act.topping_id', '=', 't.id')
            ->leftJoin('categories as c', 'act.category_id', '=', 'c.id')
            ->where('act.add_to_cart_item_id', $item->id)
            ->select(
                'act.id',
                'act.topping_id',
                'act.category_id',
                'act.variant_id',
                't.name as topping_name',
                't.price as topping_price',
                'c.name as category_name'
            )
            ->get();

        // Calculate total toppings price for this item
        $totalToppingsPrice = $toppingsData->sum('topping_price');
        
        $quantity = (int) $item->quantity;
        
        // Add toppings price to product/variant base prices
        $finalPrice = $basePrice + $totalToppingsPrice;
        $finalOriginalPrice = $baseOriginalPrice + $totalToppingsPrice;
        
        // Calculate subtotal and estimated total with toppings
        $calculatedSubtotal = $finalPrice * $quantity;
        $calculatedEstimatedTotal = $finalPrice * $quantity;
        
        // Update the cart item in database with new calculated values
        DB::table('add_to_cart_items')
            ->where('id', $item->id)
            ->update([
                'price' => $finalPrice,
                'original_price' => $finalOriginalPrice,
                'subtotal' => $calculatedSubtotal,
                'estimated_total' => $calculatedEstimatedTotal,
                'updated_at' => now()
            ]);
        
        // Update the current item object
        $item->price = $finalPrice;
        $item->original_price = $finalOriginalPrice;
        $item->subtotal = $calculatedSubtotal;
        $item->estimated_total = $calculatedEstimatedTotal;
        $item->product_base_price = $basePrice;
        $item->product_base_original_price = $baseOriginalPrice;
        $item->variant_size = $variantSize;
        
        // Store toppings data with prices for response
        $item->toppings_data = $toppingsData;
        $item->total_toppings_price = $totalToppingsPrice;
    }

    /*
    |--------------------------------------------------------------------------
    | TOTALS CALCULATION (INCLUDING TOPPINGS)
    |--------------------------------------------------------------------------
    */
    // SUBTOTAL = sum of (price * quantity)
    $subtotal = $cartItems->sum(function ($item) {
        return (float) $item->price * (int) $item->quantity;
    });
    
    // TOTAL_PRICE = sum of (original_price * quantity)
    $totalPrice = $cartItems->sum(function ($item) {
        return (float) $item->original_price * (int) $item->quantity;
    });

    // Tax calculation
    $taxRate = $cartItems->first()->tax_amount ?? 0;
    $taxAmount = ($taxRate > 0 && $taxRate < 100)
        ? ($subtotal * $taxRate) / 100
        : (float) $taxRate;

    // Tips
    $tipsFromItems = $cartItems->sum('tips');
    $finalTip = $tip > 0 ? $tip : $tipsFromItems;

    // Delivery charges
    $totalDeliveryCharges = $deliveryCharges > 0 ? $deliveryCharges : $cartItems->sum('delivery_charges');

    // ESTIMATED TOTAL = subtotal + delivery_charges + tips + tax
    $estimatedTotal = $subtotal + $totalDeliveryCharges + $finalTip + $taxAmount;

    $ctx = AppCartContext::get((int) $userId);
    $channel = AppCartContext::normalizeChannel($ctx['channel'] ?? 'regular');
    $driveInDiscount = 0;
    $driveInPercent = 0;
    if ($channel === 'drive_in') {
        $driveInPercent = app(OrderLifecycleService::class)->driveInPercent();
        $driveInDiscount = round($subtotal * ($driveInPercent / 100), 2);
        $estimatedTotal = max(0, $estimatedTotal - $driveInDiscount);
    }

    $summary = [
        'subtotal' => round($subtotal, 2),
        'total_price' => round($totalPrice, 2),
        'tax_amount' => round($taxAmount, 2),
        'tips' => round($finalTip, 2),
        'delivery_charges' => round($totalDeliveryCharges, 2),
        'drive_in_discount' => round($driveInDiscount, 2),
        'drive_in_percent' => $driveInPercent,
        'estimated_total' => round($estimatedTotal, 2),
        'channel' => $channel,
        'channel_label' => AppCartContext::channelLabel($channel),
        'wholesale_delivery_date' => $ctx['wholesale_delivery_date'] ?? null,
        'requires_pickup_time' => $channel !== 'wholesale',
        'adding_to_order_id' => $ctx['adding_to_order_id'] ?? null,
        'pickup_date' => $ctx['pickup_date'] ?? null,
        'pickup_time' => $ctx['pickup_time'] ?? null,
    ];

    /*
    |--------------------------------------------------------------------------
    | ITEMS RESPONSE
    |--------------------------------------------------------------------------
    */
    $items = $cartItems->map(function ($item) {

        // Format toppings with price
        $toppings = $item->toppings_data->map(function ($topping) {
            return [
                'id' => $topping->id,
                'topping_id' => $topping->topping_id,
                'topping_name' => $topping->topping_name,
                'topping_price' => (float) $topping->topping_price,
                'category_id' => $topping->category_id,
                'category_name' => $topping->category_name,
                'variant_id' => $topping->variant_id,
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | COMPLEMENT PRODUCT
        |--------------------------------------------------------------------------
        */
        $complement = DB::table('complementary_products')
            ->where('product_id', $item->product_id)
            ->first();

        $complementProduct = null;

        if ($complement) {
            $compProd = DB::table('products')
                ->where('id', $complement->complementary_product_id)
                ->first();

            if ($compProd) {
                $compToppings = DB::table('topping_products')
                    ->join('categories', 'topping_products.category_id', '=', 'categories.id')
                    ->where('topping_products.product_id', $compProd->id)
                    ->get(['categories.id', 'categories.name'])
                    ->map(function ($topping) {
                        return [
                            'id' => $topping->id,
                            'name' => $topping->name
                        ];
                    });

                $complementProduct = [
                    'product_id'    => $compProd->id,
                    'product_name'  => $compProd->name,
                    'product_image' => $compProd->image,
                    'toppings'      => $compToppings,
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */
        return [
            'id'                => $item->id,
            'product_id'        => $item->product_id,
            'product_name'      => $item->product?->name,
            'product_image'     => $item->product?->image,
            'variant_id'        => $item->variant_id,
            'variant_size'      => $item->variant_size,
            'product_base_price' => round($item->product_base_price ?? 0, 2),
            'product_base_original_price' => round($item->product_base_original_price ?? 0, 2),
            'total_toppings_price' => round($item->total_toppings_price, 2),
            'price'             => round((float) $item->price, 2),
            'original_price'    => round((float) $item->original_price, 2),
            'quantity'          => $item->quantity,
            'subtotal'          => round((float) $item->subtotal, 2),
            'tips'              => $item->tips,
            'tax_amount'        => $item->tax_amount,
            'estimated_total'   => round((float) $item->estimated_total, 2),
            'delivery_address'  => $item->delivery_address,
            'order_type'        => $item->order_type,
            'pickup_time'       => $item->pickup_time,
            'delivery_charges'  => $item->delivery_charges,
            'toppings'          => $toppings,
            'complement_product'=> $complementProduct,
        ];
    });

    return response()->json([
        'success' => true,
        'status_code' => 200,
        'message' => "Cart items retrieved successfully",
        'summary' => $summary,
        'context' => $ctx,
        'items' => $items
    ], 200);
}


public function deleteCartItem($id)
{
    $cartItem = AddToCartItem::find($id);

    if (!$cartItem) {
        return response()->json([
            'success' => false,
            'message' => 'Cart item not found.'
        ], 404);
    }

    // jis amount ko minus karna hai
    $amount = $cartItem->price;

    // ✅ direct DB update (no save())
    DB::table('add_to_cart_items')
        ->where('user_id', $cartItem->user_id)
        ->where('id', '!=', $cartItem->id)
        ->update([
            'subtotal'        => DB::raw("subtotal - $amount"),
            'estimated_total' => DB::raw("estimated_total - $amount"),
        ]);

    // toppings delete
    AddToCartItemTopping::where('add_to_cart_item_id', $id)->delete();

    // item delete
    $cartItem->delete();

return response()->json([
        'success' => 200,
        'message' => 'Cart item removed successfully.'
    ],200);
}

public function updateCartItemQuantity(Request $request, $id)
{
    DB::beginTransaction();

    try {
        $userId = auth()->id();

        // =========================
        // BASIC INPUTS FROM REQUEST
        // =========================
        $newQuantity = (int) $request->quantity;
        $newPrice = (float) $request->price;
        $newOriginalPrice = (float) $request->original_price;
        $newVariantId = $request->variant_id ? (int) $request->variant_id : null;  // ✅ VARIANT HANDLED

        if ($newQuantity < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Quantity must be at least 1.'
            ], 400);
        }

        // =========================
        // FIND EXISTING CART ITEM
        // =========================
        $cartItem = AddToCartItem::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found.'
            ], 404);
        }

        // =========================
        // GET TOPPINGS FROM REQUEST (IF ANY)
        // =========================
        $toppingDetails = [];
        if ($request->toppings) {
            $toppingsJson = json_decode($request->toppings, true);
            if (!empty($toppingsJson) && is_array($toppingsJson)) {
                foreach ($toppingsJson as $categoryKey => $toppingsArray) {
                    foreach ($toppingsArray as $item) {
                        if (isset($item['topping_id']) && isset($item['category_id'])) {
                            $toppingDetails[] = [
                                'topping_id'  => (int) $item['topping_id'],
                                'category_id' => (int) $item['category_id'],
                            ];
                        }
                    }
                }
            }
        }

        // =========================
        // UPDATE VARIANT IF CHANGED
        // =========================
        if ($newVariantId !== null && $cartItem->variant_id != $newVariantId) {
            $cartItem->variant_id = $newVariantId;  // ✅ VARIANT UPDATE
        }

        // =========================
        // UPDATE QUANTITY AND PRICES
        // =========================
        $cartItem->quantity = $newQuantity;
        $cartItem->price = $newPrice;
        $cartItem->original_price = $newOriginalPrice;
        $cartItem->subtotal = $newPrice;
        $cartItem->save();

        // =========================
        // UPDATE TOPPINGS IF PROVIDED
        // =========================
        if (!empty($toppingDetails)) {
            // Delete old toppings
            AddToCartItemTopping::where('add_to_cart_item_id', $cartItem->id)->delete();
            
            // Insert new toppings
            $toppingsInsert = [];
            foreach ($toppingDetails as $topping) {
                $toppingsInsert[] = [
                    'add_to_cart_item_id' => $cartItem->id,
                    'topping_id'          => $topping['topping_id'],
                    'category_id'         => $topping['category_id'],
                ];
            }
            AddToCartItemTopping::insert($toppingsInsert);
        }

        // =========================
        // UPDATE CART TOTAL (same as addToCart logic)
        // =========================
        $allCartItems = AddToCartItem::where('user_id', $userId)->get();
        $totalSubtotal = $allCartItems->sum('subtotal');
        $tips = AddToCartItem::where('user_id', $userId)
            ->where('tips', '>', 0)
            ->value('tips') ?? 0;
        $estimatedTotal = $totalSubtotal + $tips;

        AddToCartItem::where('user_id', $userId)->update([
            'subtotal'        => $totalSubtotal,
            'estimated_total' => $estimatedTotal,
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'cart_item_id' => $cartItem->id,
            'data' => [
                'quantity' => $cartItem->quantity,
                'variant_id' => $cartItem->variant_id,  // ✅ VARIANT IN RESPONSE
                'price' => round($cartItem->price, 2),
                'original_price' => round($cartItem->original_price, 2),
                'subtotal' => round($totalSubtotal, 2),
                'estimated_total' => round($estimatedTotal, 2),
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Update cart quantity error', [
            'error' => $e->getMessage(),
            'cart_item_id' => $id,
            'request' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong while updating quantity.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function getBranchInfo()
{
    try {
        $branches = DB::table('branches')
            ->select('id', 'email', 'phone_number', 'location')  // ← id add karo
            ->get();

        return response()->json([
            'status' => true,
            'data' => $branches
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}

    protected function applyStorefrontChannel(Request $request, int $userId)
    {
        $channel = AppCartContext::normalizeChannel(
            $request->input('channel', $request->boolean('wholesale') ? 'wholesale' : null)
        );
        $ctx = AppCartContext::get($userId);
        $addingId = (int) ($request->input('adding_to_order_id') ?: ($ctx['adding_to_order_id'] ?? 0));
        $pending = $addingId
            ? Order::where('id', $addingId)->where('user_id', $userId)->first()
            : null;
        $lifecycle = app(OrderLifecycleService::class);

        if ($pending && $lifecycle->isWholesale($pending) && $channel !== 'wholesale') {
            AppCartContext::clearAddToOrder($userId);
            $pending = null;
            $addingId = 0;
        }

        if ($channel === 'wholesale') {
            $date = $request->input('wholesale_delivery_date') ?: ($ctx['wholesale_delivery_date'] ?? null);
            if ($pending && $lifecycle->isWholesale($pending) && $pending->wholesale_delivery_date) {
                $date = Carbon::parse((string) $pending->wholesale_delivery_date)->toDateString();
            } elseif (!$date || !app(WholesaleScheduleService::class)->isValidDate($date)) {
                return response()->json([
                    'success' => false,
                    'status' => false,
                    'code' => 'WHOLESALE_DATE',
                    'message' => 'Please select a wholesale delivery date (Monday, Thursday or Saturday, 7:00 PM – 10:00 PM) before adding items.',
                ], 422);
            }

            AppCartContext::put($userId, [
                'channel' => 'wholesale',
                'fulfillment' => 'wholesale',
                'wholesale_delivery_date' => $date,
                'adding_to_order_id' => $addingId ?: null,
            ]);
            return null;
        }

        if ($pending && $lifecycle->isWholesale($pending)) {
            AppCartContext::clearAddToOrder($userId);
            $addingId = 0;
        }

        AppCartContext::put($userId, [
            'channel' => $channel,
            'wholesale_delivery_date' => null,
            'adding_to_order_id' => $addingId ?: ($ctx['adding_to_order_id'] ?? null),
        ]);

        return null;
    }

}
