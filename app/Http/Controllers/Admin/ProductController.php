<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\ProductVariants;
use App\Models\Topping;
use App\Models\ToppingProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
		$products = Product::all();
        return view('admin.product.create', compact('menus', 'toppings' , 'categories', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

// public function store(Request $request)
// {
//     // -------------------------
//     // Validation
//     // -------------------------
//     $request->validate([
//         'name' => 'required',
//         'menu_id' => 'required',

//         'price' => 'nullable|numeric|min:0',

//         'featured_action' => 'nullable|in:increase,decrease',
//         'featured_method' => 'nullable|in:percentage,fixed amount',
//         'featured_amount' => 'nullable|numeric|min:0',
// 		'complementary_product_id' => 'nullable|exists:products,id',

//     ]);

//     // -------------------------
//     // Priority Check
//     // -------------------------
//     $applyPriority =
//         $request->filled('featured_action') &&
//         $request->filled('featured_method') &&
//         $request->filled('featured_amount');

//     $action = $request->featured_action;
//     $method = $request->featured_method;
//     $amount = (float) $request->featured_amount;

//     // -------------------------
//     // Image Upload
//     // -------------------------
//     if ($request->hasFile('image')) {
//         $file = $request->file('image');
//         $filename = time() . '.' . $file->getClientOriginalExtension();
//         $file->move(public_path('admin/assets/images/users/'), $filename);
//         $image = 'public/admin/assets/images/users/' . $filename;
//     } else {
//         $image = 'public/admin/assets/images/users/1675332882.jpg';
//     }

//     // -------------------------
//     // ORIGINAL PRICE (ALWAYS SET)
//     // -------------------------
//     if ($request->filled('price')) {

//         // product price entered
//         $originalPrice = (float) $request->price;

//     } elseif (!empty($request->prices) && isset($request->prices[0])) {

//         // fallback → first variant price
//         $originalPrice = (float) $request->prices[0];

//     } else {

//         // final safety
//         $originalPrice = 0;
//     }

//     $finalPrice = $originalPrice;
//     $rule = null;

//     // -------------------------
//     // Apply Priority (ON ORIGINAL PRICE)
//     // -------------------------
//     if ($applyPriority) {

//         if ($method === 'percentage') {
//             $change = ($originalPrice * $amount) / 100;

//             $finalPrice = $action === 'increase'
//                 ? $originalPrice + $change
//                 : $originalPrice - $change;
//         }

//         if ($method === 'fixed amount') {
//             $finalPrice = $action === 'increase'
//                 ? $originalPrice + $amount
//                 : $originalPrice - $amount;
//         }

//         $rule = 'Priority';
//     }

//     // -------------------------
//     // Create Product
//     // -------------------------
//     $data = [
//         'menu_id' => $request->menu_id,
//         'name' => $request->name,
//         'image' => $image,

//         // calculated price
//         'price' => round(max(0, $finalPrice), 2),

//         // MASTER PRICE (never changes)
//         'original_price' => max(0, $originalPrice),

//         'rule' => $rule,
//     ];

//     if ($rule === 'Priority') {
//         $data['featured_action'] = $action;
//         $data['featured_method'] = $method;
//         $data['featured_amount'] = $amount;
//     }

//     $product = Product::create($data);

//     // -------------------------
//     // Variants (Original Price Based)
//     // -------------------------
//     if ($request->sizes && $request->prices) {

//         foreach ($request->sizes as $key => $size) {

//             if (!isset($request->prices[$key])) {
//                 continue;
//             }

//             $variantOriginalPrice = (float) $request->prices[$key];
//             $variantPrice = $variantOriginalPrice;

//             // Apply priority on ORIGINAL price
//             if ($applyPriority) {

//                 if ($method === 'percentage') {
//                     $change = ($variantOriginalPrice * $amount) / 100;

//                     $variantPrice = $action === 'increase'
//                         ? $variantOriginalPrice + $change
//                         : $variantOriginalPrice - $change;
//                 }

//                 if ($method === 'fixed amount') {
//                     $variantPrice = $action === 'increase'
//                         ? $variantOriginalPrice + $amount
//                         : $variantOriginalPrice - $amount;
//                 }
//             }

//             ProductVariants::create([
//                 'product_id' => $product->id,
//                 'size' => $size,
//                 'price' => round(max(0, $variantPrice), 2),
//                 'original_price' => max(0, $variantOriginalPrice),
//             ]);
//         }
//     }

//     // -------------------------
//     // Categories
//     // -------------------------
//     if ($request->category_id) {
//         foreach ($request->category_id as $categoryId) {
//             ToppingProduct::create([
//                 'category_id' => $categoryId,
//                 'product_id' => $product->id,
//             ]);
//         }
//     }

//     return redirect()
//         ->route('product.index')
//         ->with('message', 'Product Created Successfully');
// }

public function store(Request $request)
{
    // -------------------------
    // Validation
    // -------------------------
    $request->validate([
        'name' => 'required',
        'menu_id' => 'required',

        'price' => 'nullable|numeric|min:0',

        'featured_action' => 'nullable|in:increase,decrease',
        'featured_method' => 'nullable|in:percentage,fixed amount',
        'featured_amount' => 'nullable|numeric|min:0',
        'complementary_product_id' => 'nullable|exists:products,id',
    ]);

    // -------------------------
    // Priority Check
    // -------------------------
    $applyPriority =
        $request->filled('featured_action') &&
        $request->filled('featured_method') &&
        $request->filled('featured_amount');

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
    // ORIGINAL PRICE (ALWAYS SET)
    // -------------------------
    if ($request->filled('price')) {

        // product price entered
        $originalPrice = (float) $request->price;

    } elseif (!empty($request->prices) && isset($request->prices[0])) {

        // fallback → first variant price
        $originalPrice = (float) $request->prices[0];

    } else {

        // final safety
        $originalPrice = 0;
    }

    $finalPrice = $originalPrice;
    $rule = null;

    // -------------------------
    // Apply Priority (ON ORIGINAL PRICE)
    // -------------------------
    if ($applyPriority) {

        if ($method === 'percentage') {
            $change = ($originalPrice * $amount) / 100;

            $finalPrice = $action === 'increase'
                ? $originalPrice + $change
                : $originalPrice - $change;
        }

        if ($method === 'fixed amount') {
            $finalPrice = $action === 'increase'
                ? $originalPrice + $amount
                : $originalPrice - $amount;
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

        // calculated price
        'price' => round(max(0, $finalPrice), 2),

        // MASTER PRICE (never changes)
        'original_price' => max(0, $originalPrice),

        'rule' => $rule,
    ];

    if ($rule === 'Priority') {
        $data['featured_action'] = $action;
        $data['featured_method'] = $method;
        $data['featured_amount'] = $amount;
    }

    $product = Product::create($data);

    // -------------------------
    // Complementary Product (Pivot Table Save)
    // -------------------------
    if ($request->filled('complementary_product_id')) {
        \DB::table('complementary_products')->insert([
            'product_id' => $product->id, // new product id
            'complementary_product_id' => $request->complementary_product_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // -------------------------
    // Variants (Original Price Based)
    // -------------------------
    if ($request->sizes && $request->prices) {

        foreach ($request->sizes as $key => $size) {

            if (!isset($request->prices[$key])) {
                continue;
            }

            $variantOriginalPrice = (float) $request->prices[$key];
            $variantPrice = $variantOriginalPrice;

            // Apply priority on ORIGINAL price
            if ($applyPriority) {

                if ($method === 'percentage') {
                    $change = ($variantOriginalPrice * $amount) / 100;

                    $variantPrice = $action === 'increase'
                        ? $variantOriginalPrice + $change
                        : $variantOriginalPrice - $change;
                }

                if ($method === 'fixed amount') {
                    $variantPrice = $action === 'increase'
                        ? $variantOriginalPrice + $amount
                        : $variantOriginalPrice - $amount;
                }
            }

            ProductVariants::create([
                'product_id' => $product->id,
                'size' => $size,
                'price' => round(max(0, $variantPrice), 2),
                'original_price' => max(0, $variantOriginalPrice),
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

    return redirect()
        ->route('product.index')
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
//
// public function update(Request $request, $id)
// {
//     $request->validate([
//         'name' => 'required',
//         'menu_id' => 'required',
//         'featured_action' => 'nullable|in:increase,decrease',
//         'featured_method' => 'nullable|in:percentage,fixed amount',
//         'featured_amount' => 'nullable|numeric|min:0',
//     ]);

//     $product = Product::with('variants','category')->findOrFail($id);

//     $applyPriority = $request->filled('featured_action') && $request->filled('featured_method') && $request->filled('featured_amount');
//     $action = $request->featured_action;
//     $method = $request->featured_method;
//     $amount = (float) $request->featured_amount;

//     // Image
//     if ($request->hasFile('image')) {
//         $destination = public_path($product->image);
//         if (File::exists($destination)) File::delete($destination);
//         $file = $request->file('image');
//         $filename = time().'.'.$file->getClientOriginalExtension();
//         $file->move(public_path('admin/assets/images/users/'), $filename);
//         $product->image = 'public/admin/assets/images/users/'.$filename;
//     }

//     // Original Price for single product
//     $inputPrice = (float) $request->price;
//     if (is_null($product->original_price) || $product->original_price <= 0) {
//         $product->original_price = $inputPrice;
//     }
//     $basePrice = (float) $product->original_price;

//     $finalPrice = $basePrice;
//     if ($applyPriority) {
//         if ($method === 'percentage') {
//             $change = ($basePrice * $amount) / 100;
//             $finalPrice = $action==='increase' ? $basePrice+$change : $basePrice-$change;
//         } elseif ($method === 'fixed amount') {
//             $finalPrice = $action==='increase' ? $basePrice+$amount : $basePrice-$amount;
//         }
//     }

//     $product->name = $request->name;
//     $product->menu_id = $request->menu_id;
//     $product->price = round(max(0,$finalPrice),2);
//     $product->rule = $applyPriority ? 'Priority' : null;

//     if ($applyPriority) {
//         $product->featured_action = $action;
//         $product->featured_method = $method;
//         $product->featured_amount = $amount;
//     }

//     $product->save();

//     // ---- VARIANTS ----
//     $submittedVariantIds = $request->variant_ids ?? [];
//     $sizes = $request->sizes ?? [];
//     $base_prices = $request->base_prices ?? [];

//     // Delete removed variants
//     $variantsToDelete = $product->variants->pluck('id')->diff($submittedVariantIds);
//     ProductVariants::destroy($variantsToDelete);

//     // Update existing / add new
//     foreach ($sizes as $key => $size) {
//         $variantId = $submittedVariantIds[$key] ?? null;
//         $variantBase = round(max(0,(float)$base_prices[$key]),2);

//         $variantPrice = $variantBase;
//         if ($applyPriority) {
//             if ($method==='percentage') { $variantPrice = $action==='increase' ? $variantBase + ($variantBase*$amount/100) : $variantBase - ($variantBase*$amount/100); }
//             elseif ($method==='fixed amount') { $variantPrice = $action==='increase' ? $variantBase+$amount : $variantBase-$amount; }
//         }

//         if ($variantId) {
//             $variant = ProductVariants::find($variantId);
//             if ($variant) {
//                 $variant->update([
//                     'size'=>$size,
//                     'original_price'=>$variantBase,
//                     'price'=>round(max(0,$variantPrice),2),
//                 ]);
//             }
//         } else {
//             ProductVariants::create([
//                 'product_id'=>$product->id,
//                 'size'=>$size,
//                 'original_price'=>$variantBase,
//                 'price'=>round(max(0,$variantPrice),2),
//             ]);
//         }
//     }

//     // ---- Categories ----
//     ToppingProduct::where('product_id',$product->id)->delete();
//     if ($request->category_id) {
//         foreach ($request->category_id as $categoryId) {
//             ToppingProduct::create(['category_id'=>$categoryId,'product_id'=>$product->id]);
//         }
//     }

//     return redirect()->route('product.index')->with([
//         'status'=>true,
//         'message'=>'Product Updated Successfully'
//     ]);
// }

public function update(Request $request, $id)
{
    // -------------------------
    // Validation
    // -------------------------
    $request->validate([
        'name' => 'required',
        'menu_id' => 'required',

        'price' => 'nullable|numeric|min:0',

        'featured_action' => 'nullable|in:increase,decrease',
        'featured_method' => 'nullable|in:percentage,fixed amount',
        'featured_amount' => 'nullable|numeric|min:0',

        'complementary_product_id' => 'nullable|exists:products,id',
    ]);

    // -------------------------
    // Priority Check
    // -------------------------
    $applyPriority =
        $request->filled('featured_action') &&
        $request->filled('featured_method') &&
        $request->filled('featured_amount');

    $action = $request->featured_action;
    $method = $request->featured_method;
    $amount = (float) $request->featured_amount;

    // -------------------------
    // Find Product
    // -------------------------
    $product = Product::findOrFail($id);

    // -------------------------
    // Image Upload
    // -------------------------
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('admin/assets/images/users/'), $filename);
        $image = 'public/admin/assets/images/users/' . $filename;
    } else {
        $image = $product->image; // existing image
    }

    // -------------------------
    // ORIGINAL PRICE (ALWAYS SET)
    // -------------------------
    if ($request->filled('price')) {
        $originalPrice = (float) $request->price;
    } elseif (!empty($request->prices) && isset($request->prices[0])) {
        $originalPrice = (float) $request->prices[0];
    } else {
        $originalPrice = 0;
    }

    $finalPrice = $originalPrice;
    $rule = null;

    // -------------------------
    // Apply Priority (ON ORIGINAL PRICE)
    // -------------------------
    if ($applyPriority) {
        if ($method === 'percentage') {
            $change = ($originalPrice * $amount) / 100;
            $finalPrice = $action === 'increase'
                ? $originalPrice + $change
                : $originalPrice - $change;
        }

        if ($method === 'fixed amount') {
            $finalPrice = $action === 'increase'
                ? $originalPrice + $amount
                : $originalPrice - $amount;
        }
        $rule = 'Priority';
    }

    // -------------------------
    // Update Product
    // -------------------------
    $data = [
        'menu_id' => $request->menu_id,
        'name' => $request->name,
        'image' => $image,
        'price' => round(max(0, $finalPrice), 2),
        'original_price' => max(0, $originalPrice),
        'rule' => $rule,
    ];

    if ($rule === 'Priority') {
        $data['featured_action'] = $action;
        $data['featured_method'] = $method;
        $data['featured_amount'] = $amount;
    } else {
        $data['featured_action'] = null;
        $data['featured_method'] = null;
        $data['featured_amount'] = null;
    }

    $product->update($data);

    // -------------------------
    // Variants
    // -------------------------
    if ($request->sizes && $request->prices) {
        foreach ($request->sizes as $key => $size) {
            if (!isset($request->prices[$key])) continue;

            $variantOriginalPrice = (float) $request->prices[$key];
            $variantPrice = $variantOriginalPrice;

            if ($applyPriority) {
                if ($method === 'percentage') {
                    $change = ($variantOriginalPrice * $amount) / 100;
                    $variantPrice = $action === 'increase'
                        ? $variantOriginalPrice + $change
                        : $variantOriginalPrice - $change;
                }
                if ($method === 'fixed amount') {
                    $variantPrice = $action === 'increase'
                        ? $variantOriginalPrice + $amount
                        : $variantOriginalPrice - $amount;
                }
            }

            // Update existing variant or create new
            if (isset($request->variant_ids[$key]) && $request->variant_ids[$key]) {
                $variant = ProductVariants::find($request->variant_ids[$key]);
                if ($variant) {
                    $variant->update([
                        'size' => $size,
                        'price' => round(max(0, $variantPrice), 2),
                        'original_price' => max(0, $variantOriginalPrice),
                    ]);
                }
            } else {
                ProductVariants::create([
                    'product_id' => $product->id,
                    'size' => $size,
                    'price' => round(max(0, $variantPrice), 2),
                    'original_price' => max(0, $variantOriginalPrice),
                ]);
            }
        }
    }

    // -------------------------
    // Categories
    // -------------------------
    if ($request->category_id) {
        // delete old
        ToppingProduct::where('product_id', $product->id)->delete();

        foreach ($request->category_id as $categoryId) {
            ToppingProduct::create([
                'category_id' => $categoryId,
                'product_id' => $product->id,
            ]);
        }
    }

    // -------------------------
    // Complementary Product
    // -------------------------
    if ($request->filled('complementary_product_id')) {
        \DB::table('complementary_products')->updateOrInsert(
            ['product_id' => $product->id],
            [
                'complementary_product_id' => $request->complementary_product_id,
                'updated_at' => now(),
            ]
        );
    } else {
        // remove if null
        \DB::table('complementary_products')->where('product_id', $product->id)->delete();
    }

    return redirect()
        ->route('product.index')
        ->with('message', 'Product Updated Successfully');
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
