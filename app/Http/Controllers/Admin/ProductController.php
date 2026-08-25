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
use App\Support\MenuCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

public function index()
{
    // Blade page ko load karna, initial table empty
    $products = Product::where('featured_amount', '!=', null)->where('featured_method', '!=', null)
    ->where('featured_action', '!=', null)
    ->select('id','menu_id','name','image','original_price','price','status','is_featured','rule','on_top','featured_amount')
        ->latest()
        ->get();
    return view('admin.product.index', compact('products'));
}

public function getProducts(Request $request)
{
    $perPage = $request->get('per_page', 10);
    $page = $request->get('page', 1);
    $search = $request->get('search', '');

    $query = Product::select('id','menu_id','name','image','original_price','price','status','is_featured','rule')
        ->with(['menu:id,name','variants:id,product_id,size,original_price,price'])
        ->latest();

    if($search) {
        $query->where('name', 'like', "%{$search}%")
              ->orWhereHas('menu', function($q) use($search){
                  $q->where('name','like',"%{$search}%");
              });
    }

    $products = $query->paginate($perPage, ['*'], 'page', $page);

    $html = view('admin.product.partials.product_rows', compact('products'))->render();

    return response()->json([
        'success' => true,
        'html' => $html,
        'current_page' => $products->currentPage(),
        'last_page' => $products->lastPage(),
    ]);
} 

    public function setOnTop(Request $request)
    {
        DB::beginTransaction();

        try {
            // Pehle sab ka on_top = 0 karo
            Product::query()->update(['on_top' => 0]);

            // Selected product ka on_top = 1 karo
            Product::where('id', $request->product_id)
                ->update(['on_top' => 1]);

            DB::commit();

            return response()->json([
                'success' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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
		$products = Product::where('status', 1)->get();
        return view('admin.product.create', compact('menus', 'toppings' , 'categories', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


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
    // CHECK IF VARIANTS EXIST
    // -------------------------
    $hasVariants = !empty($request->sizes) && !empty($request->prices);

    $originalPrice = 0;
    $finalPrice = 0;
    $rule = null;

    /*
    |--------------------------------------------------------------------------
    | SINGLE PRODUCT CASE
    |--------------------------------------------------------------------------
    | Only here products table price should be set
    |--------------------------------------------------------------------------
    */
    if (!$hasVariants) {

        if ($request->filled('price')) {
            $originalPrice = (float) $request->price;
        }

        $finalPrice = $originalPrice;

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
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE PRODUCT
    |--------------------------------------------------------------------------
    | If variant product → price will be set to 0
    |--------------------------------------------------------------------------
    */
    $product = Product::create([
        'menu_id' => $request->menu_id,
        'name' => $request->name,
        'image' => $image,
        'price' => $hasVariants ? 0 : round(max(0, $finalPrice), 2),
        'original_price' => $hasVariants ? 0 : round(max(0, $originalPrice), 2),
        'rule' => $hasVariants ? null : $rule,
        'featured_action' => ($hasVariants || !$rule) ? null : $action,
        'featured_method' => ($hasVariants || !$rule) ? null : $method,
        'featured_amount' => ($hasVariants || !$rule) ? null : $amount,
    ]);
    if (Schema::hasColumn('products', 'food_menu_id') && $request->filled('food_menu_id')) {
        $product->food_menu_id = $request->food_menu_id;
        $product->save();
    }

    // -------------------------
    // Complementary Product
    // -------------------------
    if ($request->filled('complementary_product_id')) {
        DB::table('complementary_products')->insert([
            'product_id' => $product->id,
            'complementary_product_id' => $request->complementary_product_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VARIANTS SAVE (IF EXIST)
    |--------------------------------------------------------------------------
    | Only product_variants table handles price
    |--------------------------------------------------------------------------
    */
    if ($hasVariants) {

        foreach ($request->sizes as $key => $size) {

            if (!isset($request->prices[$key])) {
                continue;
            }

            $variantOriginalPrice = (float) $request->prices[$key];
            $variantFinalPrice = $variantOriginalPrice;

            if ($applyPriority) {

                if ($method === 'percentage') {
                    $change = ($variantOriginalPrice * $amount) / 100;
                    $variantFinalPrice = $action === 'increase'
                        ? $variantOriginalPrice + $change
                        : $variantOriginalPrice - $change;
                }

                if ($method === 'fixed amount') {
                    $variantFinalPrice = $action === 'increase'
                        ? $variantOriginalPrice + $amount
                        : $variantOriginalPrice - $amount;
                }
            }

            ProductVariants::create([
                'product_id' => $product->id,
                'size' => $size,
                'price' => round(max(0, $variantFinalPrice), 2),
                'original_price' => round(max(0, $variantOriginalPrice), 2),
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

    // All other products except current one
    $products = Product::where('status', 1) 
    ->get();

    // Currently selected complementary product
    $complementaryProductId = DB::table('complementary_products')
        ->where('product_id', $id)
        ->value('complementary_product_id');

    return view('admin.product.edit', compact('product', 'menus', 'categories', 'categoryIds', 'complementaryProductId', 'products'));
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
    $request->validate([
        'name' => 'required',
        'menu_id' => 'required',
        'featured_action' => 'nullable|in:increase,decrease',
        'featured_method' => 'nullable|in:percentage,fixed amount',
        'featured_amount' => 'nullable|numeric|min:0',
    ]);

    $product = Product::with('variants','category')->findOrFail($id);

    $applyPriority = $request->filled('featured_action') 
                     && $request->filled('featured_method') 
                     && $request->filled('featured_amount');

    $action = $request->featured_action;
    $method = $request->featured_method;
    $amount = (float) $request->featured_amount;

    // -------------------------
    // IMAGE UPDATE
    // -------------------------
    if ($request->hasFile('image')) {
        $destination = public_path($product->image);
        if (File::exists($destination)) File::delete($destination);
        $file = $request->file('image');
        $filename = time().'.'.$file->getClientOriginalExtension();
        $file->move(public_path('admin/assets/images/users/'), $filename);
        $product->image = 'public/admin/assets/images/users/'.$filename;
    }

    // -------------------------
    // CHECK IF PRODUCT HAS VARIANTS
    // -------------------------
    $sizes = $request->sizes ?? [];
    $base_prices = $request->base_prices ?? [];
    $hasVariants = !empty($sizes);

    // -------------------------
    // CASE 1: SINGLE PRODUCT (NO VARIANTS)
    // -------------------------
    if (!$hasVariants) {
        // ✅ FIX: Use correct form input name
        $basePrice = $request->filled('original_price') ? (float) $request->original_price : 0;
        $finalPrice = $basePrice;

        // Apply priority rule
        if ($applyPriority && $basePrice > 0) {
            if ($method === 'percentage') {
                $change = ($basePrice * $amount) / 100;
                $finalPrice = $action==='increase' ? $basePrice + $change : $basePrice - $change;
            } elseif ($method === 'fixed amount') {
                $finalPrice = $action==='increase' ? $basePrice + $amount : $basePrice - $amount;
            }
        }

        $product->original_price = round(max(0, $basePrice), 2);
        $product->price = round(max(0, $finalPrice), 2);

        // Delete all old variants if any
        $product->variants()->delete();
    }

    // -------------------------
    // CASE 2: VARIANT PRODUCT
    // -------------------------
    if ($hasVariants) {
        // Set product price to 0 when variants exist
        $product->price = 0;
        $product->original_price = 0;
        $product->rule = null;
        $product->featured_action = null;
        $product->featured_method = null;
        $product->featured_amount = null;
        
        $submittedVariantIds = $request->variant_ids ?? [];
        $variantsToDelete = $product->variants->pluck('id')->diff($submittedVariantIds);
        ProductVariants::destroy($variantsToDelete);

        // Add / update variants
        foreach ($sizes as $key => $size) {
            $variantId = $submittedVariantIds[$key] ?? null;
            $variantBase = round(max(0,(float)$base_prices[$key]), 2);

            $variantPrice = $variantBase;
            if ($applyPriority) {
                if ($method==='percentage') {
                    $variantPrice = $action==='increase' ? $variantBase + ($variantBase*$amount/100) : $variantBase - ($variantBase*$amount/100);
                } elseif ($method==='fixed amount') {
                    $variantPrice = $action==='increase' ? $variantBase+$amount : $variantBase-$amount;
                }
            }

            if ($variantId) {
                $variant = ProductVariants::find($variantId);
                if ($variant) {
                    $variant->update([
                        'size'=>$size,
                        'original_price'=>$variantBase,
                        'price'=>round(max(0,$variantPrice),2),
                    ]);
                }
            } else {
                ProductVariants::create([
                    'product_id'=>$product->id,
                    'size'=>$size,
                    'original_price'=>$variantBase,
                    'price'=>round(max(0,$variantPrice),2),
                ]);
            }
        }
    }

    // -------------------------
    // UPDATE BASIC PRODUCT INFO
    // -------------------------
    $oldMenuId = $product->menu_id;
    $product->name = $request->name;
    $product->menu_id = $request->menu_id;
    if (Schema::hasColumn('products', 'food_menu_id')) {
        if ($request->filled('food_menu_id')) {
            $product->food_menu_id = $request->food_menu_id;
        } else {
            $newMenu = Menu::find($request->menu_id);
            $oldMenu = Menu::find($oldMenuId);
            if ($newMenu && MenuCatalog::isWholesale($newMenu) && $oldMenu && !MenuCatalog::isWholesale($oldMenu)) {
                $product->food_menu_id = $oldMenuId;
            }
        }
    }
    $product->rule = $applyPriority ? 'Priority' : null;

    if ($applyPriority) {
        $product->featured_action = $action;
        $product->featured_method = $method;
        $product->featured_amount = $amount;
    } else {
        $product->featured_action = null;
        $product->featured_method = null;
        $product->featured_amount = null;
    }

    $product->save();

    // -------------------------
    // CATEGORIES
    // -------------------------
    ToppingProduct::where('product_id',$product->id)->delete();
    if ($request->category_id) {
        foreach ($request->category_id as $categoryId) {
            ToppingProduct::create(['category_id'=>$categoryId,'product_id'=>$product->id]);
        }
    }

    // -------------------------
    // COMPLEMENTARY PRODUCT
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
        \DB::table('complementary_products')->where('product_id', $product->id)->delete();
    }

    return redirect()->route('product.index')->with([
        'status'=>true,
        'message'=>'Product Updated Successfully'
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
