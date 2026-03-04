<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplementaryProduct;
use App\Models\Product;

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
}