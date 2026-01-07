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

	public function homeProducts(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|string', // number or 'all'
        ]);

        $menuId = $request->menu_id;

        // CASE: All products
        if ($menuId === 'all') {
            $products = Product::where('status', '1')->get();
        } else {
            $products = Product::where('menu_id', $menuId)
                               ->where('status', '1')
                               ->get();
        }

        // Prepare response with menu name
        $response = $products->map(function($product) {
            $menu = $product->menu; // relation
            return [
                'menu_name'    => $menu ? $menu->name : null,
                'product_name' => $product->name,
                'price'        => $product->price,
                'image'        => $product->image,
            ];
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
