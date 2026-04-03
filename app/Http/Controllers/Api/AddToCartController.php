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
        $price = (float) $request->price;                  // frontend se aayega
        $originalPrice = (float) $request->original_price; // frontend se aayega

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
            // quantity add
            $existingCartItem->quantity += $quantity;

            // FRONTEND PRICE ADD to EXISTING
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
            $item->save();
        }
    }

    // =========================
    // CALCULATE SUBTOTAL & ESTIMATED_TOTAL
    // =========================
    $subtotal = $cartItems->sum('original_price'); // original_price sum
    $totalPrice = $cartItems->sum('price');        // price sum
    $tips = $cartItems->sum('tips');
    $tax = $cartItems->first()->tax_amount ?? 0;

    $estimatedTotal = $totalPrice + $tips + $tax;

    // =========================
    // UPDATE TABLE
    // =========================
    AddToCartItem::where('user_id', $userId)->update([
        'subtotal'        => $subtotal,
        'estimated_total' => $estimatedTotal,
    ]);

    // =========================
    // SUMMARY
    // =========================
    $summary = [
        'subtotal'        => round($subtotal, 2),
        'tax_amount'      => round($tax, 2),
        'tips'            => round($tips, 2),
        'estimated_total' => round($estimatedTotal, 2),
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

        // =========================
        // CHECK FOR COMPLEMENTARY PRODUCT
        // =========================
        $complement = DB::table('complementary_products')
            ->where('product_id', $item->product_id)
            ->first();

        $complementProduct = null;
        if ($complement) {
            $compProd = DB::table('products')
                ->where('id', $complement->complementary_product_id) // correct column name
                ->first();

            if ($compProd) {
                // Fetch complement product toppings if exist
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
            'cart_item_id'       => $item->id,
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
            'complement_product' => $complementProduct,
        ];
    });

    return response()->json([
        'success' => 200,
		'message' => "Cart items retrieved successfully",
        'summary' => $summary,
        'items'   => $items
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

}
