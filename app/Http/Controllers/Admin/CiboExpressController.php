<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CiboExpress;


class CiboExpressController extends Controller
{
    //

	public function index()
{
    $ciboExpressItems = \App\Models\CiboExpress::latest()->get();

    return view('admin.ciboexpress.index', compact('ciboExpressItems'));
}

	public function edit($id)
	{
		$ciboExpressItem = \App\Models\CiboExpress::findOrFail($id);
		return view('admin.ciboexpress.edit', compact('ciboExpressItem'));	
	}


public function update(Request $request, $id)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
    ]);

    $data = CiboExpress::findOrFail($id);

    $data->title = $request->title;
    $data->description = $request->description;

   // Image Upload
if ($request->hasFile('image')) {

    // old image delete
    if ($data->image && file_exists(public_path($data->image))) {
        unlink(public_path($data->image));
    }

    $image = $request->file('image');
    $imageName = time().'.'.$image->getClientOriginalExtension();

    $image->move(public_path('admin/assets/images'), $imageName);

    $data->image = 'public/admin/assets/images/'.$imageName;
}
    $data->save();

    return redirect()->back()->with('message','Cibo Express updated successfully');
}

}
