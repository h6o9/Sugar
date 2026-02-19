<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BulkFeatureDetail;
use App\Models\Product;
use Illuminate\Http\Request;

class BulkFeatureController extends Controller
{
    //

	public function index()
	{
		$details = BulkFeatureDetail::all();
		return view('admin.bulkfeature.index', compact('details'));
	}

	public function edit($id) 
	{
		$data = BulkFeatureDetail::first();
		return view('admin.bulkfeature.edit', compact('data'));
	}

	



public function update(Request $request, $id)
{
    // 🔹 Validation
    $request->validate([
        'action' => 'required|in:increase,decrease',
        'method' => 'required|in:percentage,fixed amount',
        'amount' => 'required|numeric|min:0',
    ]);

    $action = $request->action;
    $method = $request->method;
    $amount = $request->amount;

    // 🔹 Save or update bulk history
    BulkFeatureDetail::updateOrCreate(
        ['id' => $id],
        [
            'action' => $action,
            'method' => $method,
            'amount' => $amount,
            'status' => 1,
        ]
    );

    // 🔹 Bulk update products
    Product::with('variants')->where(function ($q) {
            $q->whereNull('rule')
              ->orWhere('rule', 'bulk');
        })
        ->chunk(200, function ($products) use ($action, $method, $amount) {

            foreach ($products as $product) {

                // ❌ Skip priority products
                if ($product->rule === 'Priority') continue;

                // 🔹 Determine base price
                $basePrice = (float) $product->price;

                // اگر product.price null یا 0 ہو تو first variant price fallback کے طور پر استعمال کریں
                if (($basePrice <= 0 || is_null($product->price)) && $product->variants->count() > 0) {
                    $basePrice = (float) $product->variants->first()->price;
                }

                // 🔹 Save original price only if null
                if (is_null($product->original_price)) {
                    $product->original_price = $basePrice;
                }

				 $basePrice = $product->original_price; 

                // 🔹 Apply percentage/fixed logic
                if ($method === 'percentage') {
                    $change = ($basePrice * $amount) / 100;
                    $newPrice = $action === 'increase'
                        ? $basePrice + $change
                        : $basePrice - $change;
                } else { // fixed amount
                    $newPrice = $action === 'increase'
                        ? $basePrice + $amount
                        : $basePrice - $amount;
                }

                // 🔹 Set rule if null
                if (is_null($product->rule)) {
                    $product->rule = 'bulk';
                }

                // 🔹 Set featured fields
                $product->featured_amount = $amount;
                $product->featured_method = $method;
                $product->featured_action = $action;

                // 🔹 Save final price
                $product->price = round(max(0, $newPrice), 2);
                $product->save();

                // 🔹 Update variants too (same logic)
                if ($product->variants->count() > 0) {
                    foreach ($product->variants as $variant) {
                        $variantBase = (float) $variant->price;
						$variant->original_price = $variantBase;
						$variantBase = $variant->original_price;

                        if ($method === 'percentage') {
                            $change = ($variantBase * $amount) / 100;
                            $variantPrice = $action === 'increase'
                                ? $variantBase + $change
                                : $variantBase - $change;
                        } else {
                            $variantPrice = $action === 'increase'
                                ? $variantBase + $amount
                                : $variantBase - $amount;
                        }

                        $variant->price = round(max(0, $variantPrice), 2);
                        $variant->save();
                    }
                }
            }
        });

    return redirect()->route('bulk-feature.index')
        ->with('message','Prices updated successfully');
}

}
