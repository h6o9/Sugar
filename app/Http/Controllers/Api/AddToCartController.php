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


//         /* =========================
//            🔹 BASIC INPUTS
//         ========================== */
//         $productId   = (int) $request->product_id;
//         $productName = $request->product_name;
//         $branchId    = (int) $request->branch_id;
//         $quantity    = (int) ($request->quantity ?? 1);
//         $tips        = (float) ($request->tips ?? 0);
//         $variantId   = $request->variant_id ? (int) $request->variant_id : null;


//         /* =========================
//            🔹 PRODUCT BASE PRICE
//         ========================== */
//         // Frontend wali price ignore kar rahe hain (best practice)
//         $product = \DB::table('products')
//             ->where('id', $productId)
//             ->first(['price']);

//         if (!$product) {
//             throw new \Exception('Product not found');
//         }

//         $basePrice = (float) $product->price;
//         $effectiveBasePrice = $basePrice; // default

//         /* =========================
//            🔹 VARIANT OVERRIDE LOGIC
//         ========================== */
//         $variantDetails = null;


//         if ($variantId) {
//             $variant = \DB::table('product_variants')
//                 ->where('id', $variantId)
//                 ->first(['id', 'price', 'size']);

//             if ($variant) {
//                 // ✅ product price override
//                 $effectiveBasePrice = (float) $variant->price;

//                 $variantDetails = [
//                     'variant_id' => $variant->id,
//                     'price'      => (float) $variant->price,
//                     'size'       => $variant->size,
//                 ];
//             }
//         }

//         /* =========================
//            🔹 TOPPINGS (JSON → FLAT)
//         ========================== */
//         $toppingCategories = $request->toppings
//             ? json_decode($request->toppings, true)
//             : [];

//         $toppingTotal   = 0;
//         $toppingDetails = [];
//         $allToppingItems = [];

//         if (!empty($toppingCategories) && is_array($toppingCategories)) {
//             foreach ($toppingCategories as $categoryName => $toppingsArray) {
//                 foreach ($toppingsArray as $item) {
//                     if (isset($item['topping_id'], $item['category_id'])) {
//                         $allToppingItems[] = [
//                             'topping_id'   => (int) $item['topping_id'],
//                             'category_id'  => (int) $item['category_id'],
//                             'category_name'=> $categoryName,
//                         ];
//                     }
//                 }
//             }
//         }

//         if (!empty($allToppingItems)) {

//             $toppingIds = array_column($allToppingItems, 'topping_id');

//             $toppings = \DB::table('toppings')
//                 ->whereIn('id', $toppingIds)
//                 ->get(['id', 'name', 'price'])
//                 ->keyBy('id');

//             $relations = \DB::table('category_toppings')
//                 ->whereIn('topping_id', $toppingIds)
//                 ->get(['topping_id', 'category_id']);

//             $categoryRelations = [];
//             foreach ($relations as $rel) {
//                 $categoryRelations[$rel->topping_id][] = $rel->category_id;
//             }

//             foreach ($allToppingItems as $item) {

//                 $toppingId  = $item['topping_id'];
//                 $categoryId = $item['category_id'];

//                 if (
//                     isset($toppings[$toppingId]) &&
//                     isset($categoryRelations[$toppingId]) &&
//                     in_array($categoryId, $categoryRelations[$toppingId])
//                 ) {
//                     $toppingPrice = (float) $toppings[$toppingId]->price;

//                     $toppingTotal += $toppingPrice;

//                     $toppingDetails[] = [
//                         'topping_id'  => $toppingId,
//                         'category_id' => $categoryId,
//                         'price'       => $toppingPrice,
//                         'name'        => $toppings[$toppingId]->name,
//                     ];
//                 }
//             }
//         }

//         /* =========================
//            🔹 FINAL PRICE CALC
//         ========================== */
//         $singleItemTotal = $effectiveBasePrice + $toppingTotal;
//         $finalTotal      = ($singleItemTotal * $quantity) + $tips;

//         /* =========================
//            🔹 SAVE CART ITEM
//         ========================== */
//         $cartItem = AddToCartItem::create([
//             'user_id'      => auth()->id(),
//             'product_id'   => $productId,
//             'product_name' => $productName,
//             'branch_id'    => $branchId,
//             'quantity'     => $quantity,
//             'price'        => $finalTotal,
//             'order_type'   => $request->order_type,
//             'delivery_address' => $request->delivery_address,
//             'pickup_time'  => $request->pickup_time,
//             'tips'         => $tips,
//         ]);

//         /* =========================
//            🔹 SAVE VARIANT
//         ========================== */
//         if ($variantDetails) {
//             AddToCartItemTopping::create([
//                 'add_to_cart_item_id' => $cartItem->id,
//                 'variant_id'          => $variantDetails['variant_id'],
//             ]);
//         }

//         /* =========================
//            🔹 SAVE TOPPINGS
//         ========================== */
//         foreach ($toppingDetails as $topping) {
//             AddToCartItemTopping::create([
//                 'add_to_cart_item_id' => $cartItem->id,
//                 'topping_id'          => $topping['topping_id'],
//                 'category_id'         => $topping['category_id'],
//             ]);
//         }

//         DB::commit();

//         return response()->json([
//             'success' => true,
//             'message' => 'Item added to cart successfully',
//         ]);

//     } catch (\Exception $e) {

//         DB::rollBack();

//         Log::error('Add to cart error', [
//             'error' => $e->getMessage(),
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
        // PRICE (PRODUCT / VARIANT)
        // =========================

		$tips = AddToCartItem::where('user_id', $userId)
		->where('tips', '>', 0)
		->value('tips');


        $basePrice = 0;
        $variantDetails = null;

        if ($variantId) {
            $variant = DB::table('product_variants')->where('id', $variantId)->first(['id', 'price', 'size']);
            if (!$variant) throw new \Exception('Variant not found');

            $basePrice = (float) $variant->price;
            $variantDetails = [
                'variant_id' => $variant->id,
                'price'      => (float) $variant->price,
                'size'       => $variant->size,
            ];
        } else {
            $product = DB::table('products')->where('id', $productId)->first(['price']);
            if (!$product) throw new \Exception('Product not found');
            $basePrice = (float) $product->price;
        }

        // =========================
        // TOPPINGS + PRICE
        // =========================
        $toppingCategories = $request->toppings ? json_decode($request->toppings, true) : [];
        $toppingTotal = 0;
        $toppingDetails = [];

        if (!empty($toppingCategories) && is_array($toppingCategories)) {
            foreach ($toppingCategories as $categoryName => $toppingsArray) {
                foreach ($toppingsArray as $item) {
                    if (isset($item['topping_id'], $item['category_id'])) {
                        $toppingPrice = (float) DB::table('toppings')->where('id', $item['topping_id'])->value('price');
                        $toppingTotal += $toppingPrice;

                        $toppingDetails[] = [
                            'topping_id'  => (int) $item['topping_id'],
                            'category_id' => (int) $item['category_id'],
                        ];
                    }
                }
            }
        }

        // =========================
        // FINAL UNIT PRICE
        // =========================
        $finalUnitPrice = $basePrice + $toppingTotal;

        // =========================
        // CHECK EXISTING CART ITEM
        // =========================
        $existingCartItem = AddToCartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->where('price', $finalUnitPrice)
            ->first();

        if ($existingCartItem) {
            // UPDATE QUANTITY
            $existingCartItem->quantity += $quantity;

            // price as per quantity
            $existingCartItem->price = $finalUnitPrice * $existingCartItem->quantity;

            // subtotal = price
            $existingCartItem->subtotal = $existingCartItem->price;

            $existingCartItem->tips = $tips;
            $existingCartItem->tax_amount = 0;
            $existingCartItem->save();

            $cartItemId = $existingCartItem->id;
        } else {
            // CREATE NEW CART ITEM
            $itemPrice = $finalUnitPrice * $quantity;

            $cartItem = AddToCartItem::create([
                'user_id'         => $userId,
                'product_id'      => $productId,
                'product_name'    => $productName,
                'branch_id'       => $branchId,
                'quantity'        => $quantity,
                'price'           => $itemPrice,
                'subtotal'        => $itemPrice,
                'tax_amount'      => 0,
                'estimated_total' => $itemPrice + $tips,
                'order_type'      => $request->order_type,
                'delivery_address'=> $request->delivery_address,
                'pickup_time'     => $request->pickup_time,
                'tips'            => $tips ?? 0,
            ]);

            $cartItemId = $cartItem->id;

            // SAVE VARIANT
            if ($variantDetails) {
                AddToCartItemTopping::create([
                    'add_to_cart_item_id' => $cartItemId,
                    'variant_id'          => $variantDetails['variant_id'],
                ]);
            }
        }

        // =========================
        // SAVE TOPPINGS
        // =========================
        foreach ($toppingDetails as $topping) {
            AddToCartItemTopping::create([
                'add_to_cart_item_id' => $cartItemId,
                'topping_id'          => $topping['topping_id'],
                'category_id'         => $topping['category_id'],
            ]);
        }

        // =========================
        // UPDATE ALL CART ITEMS SUBTOTAL + ESTIMATED_TOTAL
        // =========================
        $allCartItems = AddToCartItem::where('user_id', $userId)->get();

        $totalSubtotal = $allCartItems->sum('subtotal'); // sab items ka sum
        $totalTips = $tips ?? 0;
        $estimatedTotal = $totalSubtotal + $totalTips;

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
