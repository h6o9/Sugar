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

    // 🔹 Bulk update products - صرف ان پروڈکٹس کو اپڈیٹ کریں جن کی rule null ہے
    Product::with('variants')
        ->whereNull('rule')  // صرف وہ پروڈکٹس جن کی rule null ہے
        ->orWhere('rule', 'bulk')  // یا جن کی rule 'bulk' ہے
        ->chunk(200, function ($products) use ($action, $method, $amount) {

            foreach ($products as $product) {
                
                // 🔹 Save original price if null (product کی current price کو original_price میں محفوظ کریں)
                if (is_null($product->original_price)) {
                    $product->original_price = $product->price;
                }

                // 🔹 Calculate new price based on original_price
                $basePrice = (float) $product->original_price;

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

                // 🔹 Set rule
                $product->rule = 'bulk';

                // 🔹 Set featured fields
                $product->featured_amount = $amount;
                $product->featured_method = $method;
                $product->featured_action = $action;

                // 🔹 Save final price
                $product->price = round(max(0, $newPrice), 2);
                $product->save();

                // 🔹 Update variants
                if ($product->variants->count() > 0) {
                    foreach ($product->variants as $variant) {
                        // Save original price for variant if null
                        if (is_null($variant->original_price)) {
                            $variant->original_price = $variant->price;
                        }
                        
                        // Use variant's original price for calculation
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

    return redirect()->back()->with('success', 'Prices updated successfully');
}

}
