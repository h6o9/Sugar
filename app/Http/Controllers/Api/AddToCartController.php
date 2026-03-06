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




// public function addToCart(Request $request)
// {
//     DB::beginTransaction();

//     try {
//         $userId = auth()->id();

//         // =========================
//         // BASIC INPUTS
//         // =========================
//         $productId   = (int) $request->product_id;
//         $productName = $request->product_name;
//         $branchId    = (int) $request->branch_id;
//         $quantity    = (int) ($request->quantity ?? 1);
//         $variantId   = $request->variant_id ? (int) $request->variant_id : null;


//         // =========================
//         // GET TIPS
//         // =========================
//         $tips = AddToCartItem::where('user_id', $userId)
//             ->where('tips', '>', 0)
//             ->value('tips') ?? 0;

//         // =========================
//         // GET UNIT PRICE AND ORIGINAL PRICE FROM FRONTEND
//         // =========================
//         $unitPrice = (float) $request->unit_price;          // frontend se aayega
//         $unitOriginalPrice = (float) ($request->original_price ?? $unitPrice);

//         // =========================
//         // TOPPINGS FROM FRONTEND (NO PRICE CALCULATION)
//         // =========================
//         $toppingDetails = $request->toppings
//             ? json_decode($request->toppings, true)
//             : [];

//         // =========================
//         // FINAL UNIT PRICE (frontend se hi aayega)
//         // =========================
//         $finalUnitPrice = $unitPrice;

//         // =========================
//         // CHECK EXISTING CART ITEM (product + variant + same toppings)
//         // =========================
//         $existingItems = AddToCartItem::where('user_id', $userId)
//             ->where('product_id', $productId)
//             ->where('variant_id', $variantId)
//             ->get();

//         $existingCartItem = null;

//         foreach ($existingItems as $item) {
//             $existingToppings = AddToCartItemTopping::where('add_to_cart_item_id', $item->id)
//                 ->get(['topping_id', 'category_id'])
//                 ->toArray();

//             $existingToppingIds = collect($existingToppings)->pluck('topping_id')->sort()->values()->all();
//             $currentToppingIds = collect($toppingDetails)->pluck('topping_id')->sort()->values()->all();

//             if ($existingToppingIds === $currentToppingIds) {
//                 $existingCartItem = $item;
//                 break;
//             }
//         }

//         if ($existingCartItem) {
//             // =========================
//             // UPDATE EXISTING ITEM
//             // =========================
//             $existingCartItem->quantity += $quantity;
//             $existingCartItem->price = $finalUnitPrice * $existingCartItem->quantity;
//             $existingCartItem->original_price = $unitOriginalPrice * $existingCartItem->quantity;
//             $existingCartItem->subtotal = $existingCartItem->price;
//             $existingCartItem->tips = $tips;
//             $existingCartItem->tax_amount = 0;
// 			$existingCartItem->original_price = $unitOriginalPrice ;

//             $existingCartItem->save();
//             $cartItemId = $existingCartItem->id;
//         } else {
//             // =========================
//             // CREATE NEW CART ITEM
//             // =========================
//             $itemPrice = $finalUnitPrice * $quantity;

//             $cartItem = AddToCartItem::create([
//                 'user_id'          => $userId,
//                 'product_id'       => $productId,
//                 'product_name'     => $productName,
//                 'branch_id'        => $branchId,
//                 'quantity'         => $quantity,
//                 'price'            => $itemPrice,
//                 'subtotal'         => $itemPrice,
//                 'tax_amount'       => 0,
//                 'estimated_total'  => $itemPrice + $tips,
//                 'order_type'       => $request->order_type,
//                 'delivery_address' => $request->delivery_address,
//                 'pickup_time'      => $request->pickup_time,
//                 'tips'             => $tips,
//                 'original_price'   => $unitOriginalPrice * $quantity,
//                 'variant_id'       => $variantId,
// 				'original_price' => $unitOriginalPrice 
//             ]);

//             $cartItemId = $cartItem->id;
//         }

//         // =========================
//         // SAVE TOPPINGS (NO PRICE CALCULATION)
//         // =========================
//         // =========================
// // TOPPINGS FROM FRONTEND (NO PRICE CALCULATION)
// // =========================
// $toppingDetails = [];
// if ($request->toppings) {
//     $toppingsJson = json_decode($request->toppings, true);
    
//     if (!empty($toppingsJson) && is_array($toppingsJson)) {
//         foreach ($toppingsJson as $categoryKey => $toppingsArray) {
//             if (is_array($toppingsArray)) {
//                 foreach ($toppingsArray as $item) {
//                     if (isset($item['topping_id']) && isset($item['category_id'])) {
//                         $toppingDetails[] = [
//                             'topping_id'  => (int) $item['topping_id'],
//                             'category_id' => (int) $item['category_id'],
//                         ];
//                     }
//                 }
//             }
//         }
//     }
// }

//         // =========================
//         // UPDATE CART TOTALS
//         // =========================
//         $allCartItems = AddToCartItem::where('user_id', $userId)->get();
//         $totalSubtotal = $allCartItems->sum('subtotal');
//         $estimatedTotal = $totalSubtotal + $tips;

//         AddToCartItem::where('user_id', $userId)->update([
//             'subtotal'        => $totalSubtotal,
//             'estimated_total' => $estimatedTotal,
//         ]);

//         DB::commit();

//         return response()->json([
//             'success'      => true,
//             'message'      => 'Item added to cart successfully',
//             'cart_item_id' => $cartItemId
//         ]);

//     } catch (\Exception $e) {
//         DB::rollBack();

//         Log::error('Add to cart error', [
//             'error'   => $e->getMessage(),
//             'request' => $request->all()
//         ]);

//         return response()->json([
//             'success' => false,
//             'message' => 'Something went wrong while adding to cart.',
//             'error'   => $e->getMessage(),
//         ], 500);
//     }
// }

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
        // FRONTEND CALCULATED PRICES
        // =========================
        $unitPrice = (float) $request->unit_price;
        $unitOriginalPrice = $request->has('original_price')
            ? (float) $request->original_price
            : $unitPrice;

        // frontend total item price
        $itemPrice = (float) $request->total_price;

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

                    if (is_array($toppingsArray)) {

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
                ->get(['topping_id'])
                ->toArray();

            $existingToppingIds = collect($existingToppings)->pluck('topping_id')->sort()->values()->all();
            $currentToppingIds = collect($toppingDetails)->pluck('topping_id')->sort()->values()->all();

            if ($existingToppingIds === $currentToppingIds) {
                $existingCartItem = $item;
                break;
            }
        }

        if ($existingCartItem) {

            // =========================
            // UPDATE EXISTING ITEM
            // =========================
            $existingCartItem->quantity += $quantity;

            $existingCartItem->price = $request->updated_total_price;
            $existingCartItem->original_price = $request->updated_original_price;

            $existingCartItem->subtotal = $existingCartItem->price;
            $existingCartItem->tips = $tips;
            $existingCartItem->tax_amount = 0;

            $existingCartItem->save();

            $cartItemId = $existingCartItem->id;

        } else {

            // =========================
            // CREATE NEW CART ITEM
            // =========================
            $cartItem = AddToCartItem::create([
                'user_id'          => $userId,
                'product_id'       => $productId,
                'product_name'     => $productName,
                'branch_id'        => $branchId,
                'quantity'         => $quantity,
                'price'            => $itemPrice,
                'subtotal'         => $itemPrice,
                'tax_amount'       => 0,
                'estimated_total'  => $itemPrice + $tips,
                'order_type'       => $request->order_type,
                'delivery_address' => $request->delivery_address,
                'pickup_time'      => $request->pickup_time,
                'tips'             => $tips,
                'original_price'   => $unitOriginalPrice * $quantity,
                'variant_id'       => $variantId,
            ]);

            $cartItemId = $cartItem->id;

            // =========================
            // SAVE TOPPINGS
            // =========================
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
        // UPDATE CART TOTAL
        // =========================
        $allCartItems = AddToCartItem::where('user_id', $userId)->get();

        $totalSubtotal = $allCartItems->sum('subtotal');

        $estimatedTotal = $totalSubtotal + $tips;

        AddToCartItem::where('user_id', $userId)->update([
            'subtotal'        => $totalSubtotal,
            'estimated_total' => $estimatedTotal,
        ]);

        DB::commit();

        return response()->json([
            'success'      => true,
            'message'      => 'Item added to cart successfully',
            'cart_item_id' => $cartItemId
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        Log::error('Add to cart error', [
            'error'   => $e->getMessage(),
            'request' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong while adding to cart.',
            'error'   => $e->getMessage(),
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

    $newTip = $request->tip ?? 0;

    foreach ($cartItems as $item) {
        // Adjust estimated total
        $item->estimated_total = $item->estimated_total - $item->tips + $newTip;

        // Update tips
        $item->tips = $newTip;

        $item->save();
    }

    $firstItem = $cartItems->first();

    return response()->json([
        'success' => true,
        'message' => 'Continue to payment',
            'subtotal' => $firstItem->subtotal,
            'tax_amount' => $firstItem->tax_amount,
            'tips' => $firstItem->tips,
            'estimated_total' => $firstItem->estimated_total,
    ]);
}



public function getUserCartItems()
{
    $userId = auth()->id();

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
    // LOOP EACH ITEM TO ADD BRANCH TAX ONLY IF TAX AMOUNT = 0
    // =========================
    foreach ($cartItems as $item) {
        if ($item->tax_amount == 0) {
            $branchTax = (float) DB::table('branches')
                ->where('id', $item->branch_id)
                ->value('tax') ?? 0;

            $item->tax_amount = $branchTax;
            $item->estimated_total = $item->subtotal + ($item->tips ?? 0) + $branchTax;
            $item->save();
        }
    }

    // =========================
    // SUMMARY
    // =========================
   $summary = [
    'subtotal'        => round($cartItems->first()->subtotal ?? 0, 2),
    'tax_amount'      => round($cartItems->first()->tax_amount ?? 0, 2),
    'tips'            => round($cartItems->first()->tips ?? 0, 2),
    'estimated_total' => round($cartItems->first()->estimated_total ?? 0, 2),
];

    // =========================
    // ITEMS RESPONSE
    // =========================
    $items = $cartItems->map(function ($item) {
        $variantSize = $item->toppings
            ->pluck('variant.size')
            ->filter()
            ->unique()
            ->values()
            ->first();

        return [
            'cart_item_id'    => $item->id,
            'product_id'      => $item->product_id,
            'product_name'    => $item->product?->name,
            'product_image'   => $item->product?->image,
            'variant_size'    => $variantSize,
            'unit_price'      => $item->price,
            'quantity'        => $item->quantity,
            'subtotal'        => $item->subtotal,
            'tips'            => $item->tips,
            'tax_amount'      => $item->tax_amount,
            'estimated_total' => $item->estimated_total,
            'delivery_address'=> $item->delivery_address,
            'order_type'      => $item->order_type,
            'pickup_time'     => $item->pickup_time,
        ];
    });

    return response()->json([
        'success' => true,
        'summary' => $summary,
        'items'   => $items
    ]);
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
        'success' => true,
        'message' => 'Cart item removed successfully.'
    ]);
}

}
