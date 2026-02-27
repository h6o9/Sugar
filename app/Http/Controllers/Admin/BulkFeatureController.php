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
		$data = BulkFeatureDetail::findOrFail($id);
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
    Product::with('variants')
        ->chunk(200, function ($products) use ($action, $method, $amount) {

            foreach ($products as $product) {

                // 🔹 Determine base price
                $basePrice = (float) $product->price;

                // agar product price null ya 0 ho → first variant price use karo
                if (($basePrice <= 0 || is_null($product->price)) && $product->variants->count() > 0) {
                    $basePrice = (float) $product->variants->first()->price;
                }

                // 🔹 Save original price only first time
                if (is_null($product->original_price)) {
                    $product->original_price = $basePrice;
                }

				$basePrice = $product->original_price;

                // 🔹 Apply percentage / fixed logic
                if ($method === 'percentage') {
                    $change = ($basePrice * $amount) / 100;
                    $newPrice = $action === 'increase'
                        ? $basePrice + $change
                        : $basePrice - $change;
                } else {
                    $newPrice = $action === 'increase'
                        ? $basePrice + $amount
                        : $basePrice - $amount;
                }

                // ✅ FORCE rule = bulk for ALL products
                $product->rule = 'bulk';

                // 🔹 Featured fields
                $product->featured_amount = $amount;
                $product->featured_method = $method;
                $product->featured_action = $action;

                // 🔹 Save product price
                $product->price = round(max(0, $newPrice), 2);
                $product->save();

                // 🔹 Update variants
                if ($product->variants->count() > 0) {
                    foreach ($product->variants as $variant) {

                         if (is_null($variant->original_price)) {
								$variant->original_price = $variant->price;
							}

							$variantBase = (float) $variant->original_price;

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
        ->with('message', 'Prices updated successfully');
}

}
