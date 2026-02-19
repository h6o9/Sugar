<?php

namespace App\Http\Controllers\Admin;

use App\Models\Menu;
use App\Models\Product;
use App\Models\Topping;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\ToppingProduct;
use App\Models\ProductVariants;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::with('variants')->latest()->get();
        return view('admin.product.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menus = Menu::all();
        $toppings = Topping::all();
        $categories = Category::all();
        return view('admin.product.create', compact('menus', 'toppings' , 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
// 	public function store(Request $request)
// {
//     $request->validate([
//         'name' => 'required',
//         'menu_id' => 'required',

//         'action' => 'nullable|in:increase,decrease',
//         'method' => 'nullable|in:percentage,fixed amount',
//         'amount' => 'nullable|numeric|min:0',
//     ]);

//     /*
//     |--------------------------------------------------------------------------
//     | Image Upload
//     |--------------------------------------------------------------------------
//     */
//     if ($request->hasFile('image')) {
//         $file = $request->file('image');
//         $filename = time().'.'.$file->getClientOriginalExtension();
//         $file->move(public_path('admin/assets/images/users/'), $filename);
//         $image = 'public/admin/assets/images/users/'.$filename;
//     } else {
//         $image = 'public/admin/assets/images/users/1675332882.jpg';
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | BASE PRICE
//     |--------------------------------------------------------------------------
//     */

//     $currentPrice = (float) $request->price;
//     $finalPrice   = $currentPrice;
//     $rule         = null;

//     /*
//     |--------------------------------------------------------------------------
//     | ✅ PRIORITY APPLY (EVEN IF BULK EXISTED BEFORE)
//     |--------------------------------------------------------------------------
//     */

//     if (
//         $request->filled('action') &&
//         $request->filled('method') &&
//         $request->filled('amount')
//     ) {

//         $action = $request->action;
//         $method = $request->method;
//         $amount = (float) $request->amount;

//         // percentage
//         if ($method === 'percentage') {

//             $change = ($currentPrice * $amount) / 100;

//             $finalPrice = $action === 'increase'
//                 ? $currentPrice + $change
//                 : $currentPrice - $change;
//         }

//         // fixed
//         if ($method === 'fixed amount') {

//             $finalPrice = $action === 'increase'
//                 ? $currentPrice + $amount
//                 : $currentPrice - $amount;
//         }

//         // 🔥 override bulk
//         $rule = 'Priority';
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | CREATE PRODUCT
//     |--------------------------------------------------------------------------
//     */

//     $data = [
//         'menu_id' => $request->menu_id,
//         'name' => $request->name,
//         'image' => $image,
//         'price' => round(max(0, $finalPrice), 2),
//         'rule'  => $rule,
//     ];

//     if ($rule === 'Priority') {
//         $data['featured_action'] = $request->action;
//         $data['featured_method'] = $request->method;
//         $data['featured_amount'] = $request->amount;
//     }

//     $product = Product::create($data);

//     /*
//     |--------------------------------------------------------------------------
//     | Variants
//     |--------------------------------------------------------------------------
//     */
//     if ($request->sizes && $request->prices) {
//         foreach ($request->sizes as $key => $size) {
//             ProductVariants::create([
//                 'product_id' => $product->id,
//                 'size' => $size,
//                 'price' => $request->prices[$key],
//             ]);
//         }
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | Categories
//     |--------------------------------------------------------------------------
//     */
//     if ($request->category_id) {
//         foreach ($request->category_id as $categoryId) {
//             ToppingProduct::create([
//                 'category_id' => $categoryId,
//                 'product_id' => $product->id,
//             ]);
//         }
//     }

//     return redirect()->route('product.index')
//         ->with('message','Product Created Successfully');
// }

public function store(Request $request)
{
    // -------------------------
    // Validation
    // -------------------------
    $request->validate([
        'name' => 'required',
        'menu_id' => 'required',
        'featured_amount' => 'nullable|in:increase,decrease',
        'featured_method' => 'nullable|in:percentage,fixed amount',
        'featured_amount' => 'nullable|numeric|min:0',
    ]);
      
	$action = $request->featured_action;
	$method = $request->featured_method;
	$amount = (float) $request->featured_amount;


    // -------------------------
    // Image Upload
    // -------------------------
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('admin/assets/images/users/'), $filename);
        $image = 'public/admin/assets/images/users/' . $filename;
    } else {
        $image = 'public/admin/assets/images/users/1675332882.jpg';
    }

    // -------------------------
    // Base Price
    // -------------------------
    $currentPrice = (float) $request->price;
    $finalPrice = $currentPrice;
    $rule = null;

    $applyPriority = $request->featured_amount &&
                      $request->featured_method &&
                      $request->featured_amount;
					  

if ($applyPriority) {
	    $action = $request->featured_action;
        $method = $request->featured_method;
        $amount = (float) $request->featured_amount;

        // Calculate main product price
        if ($method === 'percentage') {
            $change = ($currentPrice * $amount) / 100;
            $finalPrice = $action === 'increase'
                ? $currentPrice + $change
                : $currentPrice - $change;
        }

        if ($method === 'fixed amount') {
            $finalPrice = $action === 'increase'
                ? $currentPrice + $amount
                : $currentPrice - $amount;
        }

        $rule = 'Priority';
    }

    // -------------------------
    // Create Product
    // -------------------------
    $data = [
        'menu_id' => $request->menu_id,
        'name' => $request->name,
        'image' => $image,
        'price' => round(max(0, $finalPrice), 2),
        'original_price' => $currentPrice,
        'rule' => $rule,
    ];

    if ($rule === 'Priority') {
        $data['featured_action'] = $request->action;
        $data['featured_method'] = $request->method;
        $data['featured_amount'] = $request->amount;
    }

    $product = Product::create($data);

    // -------------------------
    // Variants (apply Priority if exists)
    // -------------------------
    if ($request->sizes && $request->prices) {
        foreach ($request->sizes as $key => $size) {
            $variantPrice = (float) $request->prices[$key];
            $variantOriginalPrice = $variantPrice;

            // Apply priority rule on variant price
            if ($applyPriority) {
                if ($method === 'percentage') {
                    $change = ($variantPrice * $amount) / 100;
                    $variantPrice = $action === 'increase'
                        ? $variantPrice + $change
                        : $variantPrice - $change;
                }

                if ($method === 'fixed amount') {
                    $variantPrice = $action === 'increase'
                        ? $variantPrice + $amount
                        : $variantPrice - $amount;
                }
            }

            ProductVariants::create([
                'product_id' => $product->id,
                'size' => $size,
                'price' => round(max(0, $variantPrice), 2),
                'original_price' => $variantOriginalPrice,
            ]);
        }
    }

    // -------------------------
    // Categories
    // -------------------------
    if ($request->category_id) {
        foreach ($request->category_id as $categoryId) {
            ToppingProduct::create([
                'category_id' => $categoryId,
                'product_id' => $product->id,
            ]);
        }
    }

    return redirect()->route('product.index')
        ->with('message', 'Product Created Successfully');
}



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::with('menu', 'variants','category')->find($id);
        $menus = Menu::all();
        $categoryIds = $product->category->pluck('category_id')->toArray();
        $categories = Category::all();
        return view('admin.product.edit', compact('product', 'menus', 'categories', 'categoryIds'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
public function update(Request $request, $id)
{
    // -------------------------
    // Validation
    // -------------------------
    $request->validate([
        'name' => 'required',
        'menu_id' => 'required',
        'featured_action' => 'nullable|in:increase,decrease',
        'featured_method' => 'nullable|in:percentage,fixed amount',
        'featured_amount' => 'nullable|numeric|min:0',
    ]);

	$action = $request->featured_action;
	$method = $request->featured_method;
	$amount = (float) $request->featured_amount;

    $product = Product::with('variants', 'category')->find($id);

    // -------------------------
    // Image Upload
    // -------------------------
    if ($request->hasFile('image')) {
        $destination = public_path($product->image);
        if (File::exists($destination)) {
            File::delete($destination);
        }

        $file = $request->file('image');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('admin/assets/images/users/'), $filename);
        $product->image = 'public/admin/assets/images/users/' . $filename;
    }

    // -------------------------
    // Base Price
    // -------------------------
    $currentPrice = (float) $request->price;
    $finalPrice = $currentPrice;
    $rule = null;

    $applyPriority =    $applyPriority = $request->featured_amount &&
                      $request->featured_method &&
                      $request->featured_amount;
					  
    if ($applyPriority) {
        $action = $request->featured_action;
        $method = $request->featured_method;
        $amount = (float) $request->featured_amount;

        if ($method === 'percentage') {
            $change = ($currentPrice * $amount) / 100;
            $finalPrice = $action === 'increase'
                ? $currentPrice + $change
                : $currentPrice - $change;
        }

        if ($method === 'fixed amount') {
            $finalPrice = $action === 'increase'
                ? $currentPrice + $amount
                : $currentPrice - $amount;
        }

        $rule = 'Priority';
    }

    // -------------------------
    // Update Product
    // -------------------------
    $product->menu_id = $request->menu_id;
    $product->name = $request->name;
    $product->price = round(max(0, $finalPrice), 2);
	if (is_null($product->original_price)) {
		$product->original_price = $currentPrice;
	}    
	$product->rule = $rule;

    if ($rule === 'Priority') {
        $product->featured_action = $request->featured_action;
        $product->featured_method = $request->featured_method;
        $product->featured_amount = $request->featured_amount;
    }

    $product->save();

    // -------------------------
    // Update Variants
    // -------------------------
    if ($request->sizes && $request->prices) {
        foreach ($request->sizes as $key => $size) {
            $variantPrice = (float) $request->prices[$key];
            $variantOriginalPrice = $variantPrice;

            if ($applyPriority) {
                if ($method === 'percentage') {
                    $change = ($variantPrice * $amount) / 100;
                    $variantPrice = $action === 'increase'
                        ? $variantPrice + $change
                        : $variantPrice - $change;
                }

                if ($method === 'fixed amount') {
                    $variantPrice = $action === 'increase'
                        ? $variantPrice + $amount
                        : $variantPrice - $amount;
                }
            }

            if (isset($product->variants[$key])) {
                $product->variants[$key]->update([
                    'size' => $size,
                    'price' => round(max(0, $variantPrice), 2),
                    'original_price' => $variantOriginalPrice,
                ]);
            } else {
                ProductVariants::create([
                    'product_id' => $product->id,
                    'size' => $size,
                    'price' => round(max(0, $variantPrice), 2),
                    'original_price' => $variantOriginalPrice,
                ]);
            }
        }
    }

    // -------------------------
    // Update Categories
    // -------------------------
    if ($product->category->count() > 0) {
        foreach ($product->category as $topping) {
            $topping->delete();
        }
    }

    if ($request->category_id) {
        foreach ($request->category_id as $categoryId) {
            ToppingProduct::create([
                'category_id' => $categoryId,
                'product_id' => $product->id,
            ]);
        }
    }

    return redirect()->route('product.index')->with([
        'status' => true,
        'message' => 'Product Updated Successfully'
    ]);
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Product::destroy($id);
        return redirect()->route('product.index')->with(['status' => true, 'message' => 'Product Deleted Successfully']);
    }

    public function status($id)
    {
        /*update status */

        $product = Product::find($id);
        $product->update(['status' => $product->status == 0 ? '1' : '0']);
        return redirect()->back()->with(['status' => true, 'message' => 'Updated Successfully']);
    }

    public function toggleFeatured($id)
    {
        $product = Product::findOrFail($id);
        $product->is_featured = !$product->is_featured;
        $product->save();

        $message = $product->is_featured
            ? 'Product marked as Featured successfully!'
            : 'Product unfeatured successfully!';

        return redirect()->back()->with('message', $message);
    }

}
