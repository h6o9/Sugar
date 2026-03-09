<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplementaryProduct;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;  

class ComplementaryProductController extends Controller
{
    public function index()
    {
        $details = ComplementaryProduct::with(['product', 'complementary'])
                    ->latest()
                    ->get();

        return view('admin.bundleoffer.index', compact('details'));
    }

    public function destroy($id)
    {
        $record = ComplementaryProduct::findOrFail($id);
        $record->delete();

        return redirect()->back()->with('message', 'Complementary Product Deleted Successfully');
    }


public function search(Request $request)
{
    try {

        $perPage = 100;
        $page = $request->get('page', 1);
        $search = $request->get('search');

        $products = Product::query()
            ->select('id','name')
            ->where('status',1)
            ->when($search, function($q) use ($search){
                $q->where('name','LIKE',"%{$search}%");
            })
            ->orderBy('name')
            ->paginate($perPage,['*'],'page',$page);

        $formattedProducts = [];
        foreach($products as $product) {
            $formattedProducts[] = [
                'id' => $product->id,
                'text' => $product->name
            ];
        }

        return response()->json([
            'results' => $formattedProducts,
            'more' => $products->hasMorePages()
        ]);

    } catch (\Throwable $e) {

        \Log::error('Product Search Error: '.$e->getMessage());

        return response()->json([
            'results' => [],
            'more' => false
        ],500);
    }
}
}