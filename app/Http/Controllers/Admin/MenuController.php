<?php

namespace App\Http\Controllers\Admin;

use App\Models\Menu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menus = Menu::orderBy('id' , 'DESC')->get();
        return view('admin.menus.index', compact('menus'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:menus,name',
        ]);

        $menu = Menu::create([
            'name' => $request->name,
            'type' => $request->input('type', 'food'),
            'icon' => $request->input('icon'),
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);
        return redirect()->route('menu.index')->with(['status'=> true ,'message'=>'Created Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $menu = Menu::find($id);
        return view('admin.menus.edit',compact('menu'));
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
        ]);
        $category = Menu::find($id);

        $category->update([
            'name' => $request->name,
            'type' => $request->input('type', $category->type),
            'icon' => $request->input('icon', $category->icon),
            'sort_order' => (int) $request->input('sort_order', $category->sort_order),
        ]);
        return redirect()->route('menu.index')->with(['status' => true, 'message' => 'Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Menu::destroy($id);
        return redirect()->route('menu.index')->with(['status' => true, 'message' => 'Deleted Successfully']);
    }
}
