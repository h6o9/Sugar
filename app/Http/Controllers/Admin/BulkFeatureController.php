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
    Product::where(function ($q) {
            $q->whereNull('rule')
              ->orWhere('rule', 'bulk');
        })
        ->chunk(200, function ($products) use ($action,$method,$amount) {

            foreach ($products as $product) {

                // ❌ Skip priority products
                if ($product->rule === 'Priority') continue;

                $currentPrice = (float) $product->price;

                // Save original price only first time
                if (is_null($product->original_price)) {
                    $product->original_price = $currentPrice;
                }

                $basePrice = $product->price;

                // Percentage calculation
                if ($method === 'percentage') {
                    $change = ($basePrice * $amount) / 100;
                    $newPrice = $action === 'increase'
                        ? $basePrice + $change
                        : $basePrice - $change;
                }

                // Fixed amount calculation
                if ($method === 'fixed amount') {
                    $newPrice = $action === 'increase'
                        ? $basePrice + $amount
                        : $basePrice - $amount;
                }

                // Set rule automatically if null
                if (is_null($product->rule)) {
                    $product->rule = 'bulk';
                }

				$product->featured_amount = $amount;
				$product->featured_method = $method;
				$product->featured_action = $action;

                // Save final price (rounded)
                $product->price = round(max(0, $newPrice), 2);

                $product->save();
            }
        });

    return redirect()->route('bulk-feature.index')
        ->with('message','Prices updated successfully');
}

}
