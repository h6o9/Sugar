<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OrderComplationReward;

class OrderCompletionController extends Controller
{
    //

	public function orderCompletion()
	{
		$data = OrderComplationReward::all();
		return view('admin.order_completion.index', compact('data'));
	}

	public function EditorderCompletion()
	{
		$data = OrderComplationReward::first();
		return view('admin.order_completion.edit', compact('data'));
	}

	public function UpdateorderCompletion(Request $request, $id)
	{
		$request->validate([
			'points' => 'required',
		]);

		$data = OrderComplationReward::find($id);
		$data->points = $request->points;
		$data->save();

		return redirect()->route('order-completion')->with(['status' => true, 'message' => 'Updated Successfully']);
	}
}
