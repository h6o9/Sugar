<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


class FilterController extends Controller
{
    //


public function filterData(Request $request)
{
    $name = $request->input('name');

   $products = DB::table('products')
    ->join('menus', 'products.menu_id', '=', 'menus.id')
    ->where('products.name', 'LIKE', '%' . $name . '%')
    ->select(
        'products.id as product_id',   // ✅ product id
        'menus.name as menu_name',
        'products.name as product_name',
        'products.image',
        'products.price'
    )
    ->get();


    if ($products->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'Product not found',
            'data' => []
        ], 404);
    }

    return response()->json([
        'status' => true,
        'message' => 'Products fetched successfully',
        'data' => $products
    ], 200);
}


}
