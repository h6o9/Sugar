<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branch;
use Illuminate\Http\Request;
use App\Mail\RegisterSuccessMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class BranchController extends Controller
{
    public function view()
    {
        $branches = Branch::orderBy('id', 'DESC')->get();
        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {

        return view('admin.branches.create');
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:branches',
            'phone_number' => 'required',
            'branch_number' => 'required',
            'location' => 'required',
        ]);
        $password = random_int(10000000, 99999999);
        $product = Branch::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'branch_number' => $request->branch_number,
            'location' => $request->location,
            'tax' => $request->tax,
            'password' => Hash::make($password),
        ]);
        $message['name'] = $request->name;
        $message['email'] = $request->email;
        $message['password'] = $password;
        try {
            Mail::to($request->email)->send(new RegisterSuccessMail($message));
            return redirect()->route('branches.index')->with(['status' => true, 'message' => 'Created successfully']);
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return back()->with(['status' => false, 'message' => $th->getMessage()]);
        }

    }

    public function edit($id)
    {
        $branch = Branch::find($id);
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'phone_number' => 'required',
            // 'branch_number' => 'required',
            'location' => 'required',
            'tax' => 'required',
        ]);
        // return $request->all();
        $branch = Branch::find($id);
        $branch->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'branch_number' => $request->branch_number,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'tax' => $request->tax,
            'is_orderable' => $request->boolean('is_orderable'),
            'city_label' => $request->city_label,
        ]);
        return redirect()->route('branches.index')->with(['status' => true, 'message' => 'Updated successfully']);
    }

    public function destroy($id){
        Branch::destroy($id);
        return redirect()->back()->with(['status' => true, 'message' => 'Deleted Successfully']);

    }
}
