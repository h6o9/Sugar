<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ProductDetailsController extends Controller
{
    //

public function getProductDetails($id) {
    $product = Product::find($id);

    if (!$product) {
        return response()->json([
            'status' => 'error',
            'message' => 'Product not found'
        ], 404);
    }

    // Get Product Variants
    $variants = DB::table('product_variants')
        ->where('product_id', $id)
        ->select('id', 'size', 'price', 'original_price')
        ->get();

    // Get Toppings assigned to this product
    $toppingProducts = DB::table('topping_products')
        ->where('product_id', $id)
        ->get();

    $toppingsData = [];

	$branches = DB::table('branches')->where('status', 1)->first();

    if ($toppingProducts->isNotEmpty()) {
        foreach ($toppingProducts as $tp) {
            $categoryId = $tp->category_id;

            // Get category name
            $category = DB::table('categories')->where('id', $categoryId)->first();
            $categoryName = $category ? $category->name : 'Unknown';

            // Get toppings for this category from category_toppings
            $categoryToppings = DB::table('category_toppings')
                ->where('category_id', $categoryId)
                ->join('toppings', 'category_toppings.topping_id', '=', 'toppings.id')
                ->select('toppings.id as topping_id', 'toppings.name as topping_name', 'toppings.price as topping_price')
                ->get();

            if ($categoryToppings->isNotEmpty()) {
                foreach ($categoryToppings as $topping) {
                    // Group by category name
                    $toppingsData[$categoryName][] = [
                        'category_id' => $categoryId,
                        'topping_id' => $topping->topping_id,
                        'topping_name' => $topping->topping_name,
                        'topping_price' => $topping->topping_price,
                    ];
                }
            }
        }
    }

    return response()->json([
        'status' => 'success',
        'data' => [
            'id' => $product->id,
			'branch_id' => $branches->id,
			'branches_name' => $branches->name,
            'name' => $product->name,
            'price' => $product->price,
			'original_price' => $product->original_price,
            'image' => $product->image,
            'variants' => $variants,     // Product variants
            'toppings' => $toppingsData  // Category wise toppings
        ]
    ]);
}


}
