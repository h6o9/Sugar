<?php

namespace App\Http\Controllers\Api;

use App\Models\Menu;
use App\Models\Product;
use App\Models\Topping;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    //

	// public function homeProducts(Request $request)
    // {
    //     $request->validate([
    //         'menu_id' => 'required|string', // number or 'all'
    //     ]);

    //     $menuId = $request->menu_id;

    //     // CASE: All products
    //     if ($menuId === 'all') {
    //         $products = Product::where('status', '1')->get();
    //     } elseif($menuId === "featured"){

	// 	$products = Product::where('is_featured', '1')->get();

	// 	} else {
    //         $products = Product::where('menu_id', $menuId)
    //                            ->get();
    //     }

    //     // Prepare response with menu name
    //     $response = $products->map(function($product) {
    //         $menu = $product->menu; // relation
    //         return [
    //             'menu_name'    => $menu ? $menu->name : null,
    //             'product_name' => $product->name,
    //             'price'        => $product->price,
    //             'image'        => $product->image,
    //         ];
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'data'   => $response
    //     ]);
    // }

public function homeProducts(Request $request)
{
    $request->validate([
        'menu_id' => 'required|string',
    ]);

    $menuId = $request->menu_id;

    // CASE: All products
    if ($menuId === 'all') {

        $products = Product::where('status', '1')
            ->whereIn('rule', ['bulk','Priority'])
            ->where('featured_action', 'decrease')
            ->get();

    } elseif ($menuId === "featured") {

        $products = Product::where('is_featured', '1')
            ->whereIn('rule', ['bulk','Priority'])
            ->where('featured_action', 'decrease')
            ->get();

    } else {

        $products = Product::where('menu_id', $menuId)
            ->whereIn('rule', ['bulk','Priority'])
            ->where('featured_action', 'decrease')
            ->get();
    }

    // Prepare response (same as before + condition fields)
    $response = $products->map(function ($product) {

        $menu = $product->menu;

        $data = [
            'menu_name'    => $menu ? $menu->name : null,
            'product_name' => $product->name,
            'price'        => $product->price,
            'image'        => $product->image,
        ];

        // ✅ Only when rule applied
        if ($product->featured_action === 'decrease') {

            // percentage case
            if ($product->featured_method === 'percentage') {
                $data['discount_percentage'] = $product->featured_amount;
                $data['original_price'] = $product->original_price;
                $data['current_price']  = $product->price;
            }

            // fixed amount case
            if ($product->featured_method === 'fixed amount') {
                $data['original_price'] = $product->original_price;
                $data['current_price']  = $product->price;
            }
        }

        return $data;
    });

    return response()->json([
        'status' => true,
        'data'   => $response
    ]);
}

	public function Menueitems() {
		$menueitems = Menu::get();
		
		return response()->json([
			'status' => true,
			'data'   => $menueitems
		]);
	}

	public function toppings() {
		$toppings = Topping::get();
		return response()->json([
			'status' => true,
			'data'   => $toppings
		]);
	}
}
