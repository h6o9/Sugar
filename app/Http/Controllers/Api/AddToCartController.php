<?php

namespace App\Http\Controllers\Api;

use Log;
use Exception;
use App\Models\Topping;
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

        // =========================
        // BASIC INPUTS
        // =========================
        $productId   = (int) $request->product_id;
        $productName = $request->product_name;
        $branchId    = (int) $request->branch_id;
        $quantity    = (int) ($request->quantity ?? 1);
        $variantId   = $request->variant_id ? (int) $request->variant_id : null;

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
                ->pluck('topping_id')->sort()->values()->toArray();
            $currentToppings = collect($toppingDetails)->pluck('topping_id')->sort()->values()->toArray();
            if ($existingToppings === $currentToppings) {
                $existingCartItem = $item;
                break;
            }
        }

        // =========================
        // EXISTING ITEM
        // =========================
        if ($existingCartItem) {
            $existingCartItem->quantity += $quantity;
            $existingCartItem->price += $price;
            $existingCartItem->original_price += $originalPrice;
            $existingCartItem->subtotal = $existingCartItem->price;
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
        // FIX: UPDATE ONLY THE SUBTOTAL AND ESTIMATED TOTAL FOR CURRENT ITEM
        // NOT ALL ITEMS
        // =========================
        $cartItemToUpdate = AddToCartItem::find($cartItemId);
        $cartItemToUpdate->subtotal = $cartItemToUpdate->price;
        $cartItemToUpdate->estimated_total = $cartItemToUpdate->price + $tips;
        $cartItemToUpdate->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'cart_item_id' => $cartItemId
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Add to cart error', [
            'error' => $e->getMessage(),
            'request' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong while adding to cart.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
// cotinue to  payments method

public function proceedToPayment(Request $request)
{
    $userId = auth()->user()->id;

    $cartItems = AddToCartItem::where('user_id', $userId)->get();

    if ($cartItems->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No items in cart to proceed to payment.'
        ], 400);
    }

    $tips = $request->tip ?? 0;
    $deliveryCharges = 1.5;

    // =========================
    // CALCULATIONS
    // =========================
    $subtotal = $cartItems->sum('price');           // discounted price sum
    $originalPriceSum = $cartItems->sum('original_price'); // original price sum
    $tax = $cartItems->first()->tax_amount ?? 0;

    $estimatedTotal = $subtotal + $tax + $tips + $deliveryCharges;
    $originalEstimatedAmount = $originalPriceSum + $tax + $tips + $deliveryCharges;

    // =========================
    // UPDATE CART TABLE
    // =========================
    foreach ($cartItems as $item) {

        $item->tips = $tips;
        $item->subtotal = $subtotal;
        $item->estimated_total = $estimatedTotal;

        $item->save();
    }

    return response()->json([
        'success' => true,
        'message' => 'Continue to payment',

        'summary' => [
            'subtotal' => round($subtotal,2),
            'original_price_sum' => round($originalPriceSum,2),
            'tax_amount' => round($tax,2),
            'delivery_charges' => round($deliveryCharges,2),
            'tips' => round($tips,2),

            'estimated_total' => round($estimatedTotal,2),
            'original_estimated_amount' => round($originalEstimatedAmount,2),
        ]
    ]);
}


public function getUserCartItems(Request $request)
{
    $userId = auth()->id();

    // Get delivery charges and tip from request
    $deliveryCharges = (float) $request->input('delivery_charges', 0);
    $tip = (float) $request->input('tip', 0);

    $cartItems = AddToCartItem::with([
            'product:id,name,image',
            'toppings.variant:id,size'
        ])
        ->where('user_id', $userId)
        ->get();

    if ($cartItems->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No items found in your cart.'
        ], 404);
    }

    // =========================
    // SIRF CALCULATE KAREIN, UPDATE NA KAREIN
    // =========================
    
    // Calculate subtotal (original prices sum)
    $subtotal = $cartItems->sum('original_price');
    
    // Calculate total price (final prices sum)
    $totalPrice = $cartItems->sum('price');
    
    // Get tax from first item (assuming same tax for all items)
    $taxRate = $cartItems->first()->tax_amount ?? 0;
    
    // Calculate total tax amount
    $taxAmount = 0;
    if ($taxRate > 0 && $taxRate < 100) {
        // Tax is percentage
        $taxAmount = ($totalPrice * $taxRate) / 100;
    } else {
        // Tax is fixed amount
        $taxAmount = $taxRate;
    }
    
    // Calculate tips (use request tip or sum from items)
    $tipsFromItems = $cartItems->sum('tips');
    $finalTip = $tip > 0 ? $tip : $tipsFromItems;
    
    // Calculate estimated total
    $estimatedTotal = $totalPrice + $finalTip + $taxAmount + $deliveryCharges;
    
    // Validate total is reasonable
    if ($estimatedTotal > 99999999.99 || $estimatedTotal < 0) {
        \Log::warning('Unusual cart total calculated', [
            'user_id' => $userId,
            'estimated_total' => $estimatedTotal,
            'total_price' => $totalPrice,
            'tips' => $finalTip,
            'tax' => $taxAmount,
            'delivery' => $deliveryCharges
        ]);
    }
    
    // Prepare summary
    $summary = [
        'subtotal' => round($subtotal, 2),
        'total_price' => round($totalPrice, 2),
        'tax_amount' => round($taxAmount, 2),
        'tips' => round($finalTip, 2),
        'delivery_charges' => round($deliveryCharges, 2),
        'estimated_total' => round($estimatedTotal, 2),
    ];

    // Prepare items response
    $items = $cartItems->map(function ($item) {
        $variantSize = $item->toppings
            ->pluck('variant.size')
            ->filter()
            ->unique()
            ->values()
            ->first();

        // Check for complementary product
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
                    ->get([
                        'categories.id',
                        'categories.name'
                    ]);

                $complementProduct = [
                    'product_id'    => $compProd->id,
                    'product_name'  => $compProd->name,
                    'product_image' => $compProd->image,
                    'toppings'      => $compToppings,
                ];
            }
        }

        return [
            'id'       => $item->id,
            'product_id'         => $item->product_id,
            'product_name'       => $item->product?->name,
            'product_image'      => $item->product?->image,
            'variant_size'       => $variantSize,
            'price'              => $item->price,
            'original_price'     => $item->original_price,
            'quantity'           => $item->quantity,
            'subtotal'           => $item->subtotal,
            'tips'               => $item->tips,
            'tax_amount'         => $item->tax_amount,
            'estimated_total'    => $item->estimated_total,
            'delivery_address'   => $item->delivery_address,
            'order_type'         => $item->order_type,
            'pickup_time'        => $item->pickup_time,
            'delivery_charges'   => $item->delivery_charges,
            'complement_product' => $complementProduct,
        ];
    });

    return response()->json([
        'success' => true,
        'status_code' => 200,
        'message' => "Cart items retrieved successfully",
        'summary' => $summary,
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
            ->select('email', 'phone_number', 'location')
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

}
