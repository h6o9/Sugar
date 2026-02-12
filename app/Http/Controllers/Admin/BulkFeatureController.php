<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\BulkFeatureDetail;
use App\Http\Controllers\Controller;

class BulkFeatureController extends Controller
{
    //

	public function index()
	{
		$details = BulkFeatureDetail::first();
		return view('admin.bulkfeature.index', compact('details'));
	}
}
