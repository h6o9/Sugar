<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Reward;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

public function userView()
{
    $users = User::withCount([
            'orders as completed_orders_count' => function ($q) {
                $q->where('status', 'Delivered');
            }
        ])
        ->withSum([
            'orders as completed_orders_total' => function ($q) {
                $q->where('status', 'Delivered');
            }
        ], 'total_amount')
        ->select('id','name','email','phone','postcode','address')
        ->latest()
        ->get();

    // average spend calculate
    $users->transform(function ($user) {

        $user->average_spend = $user->completed_orders_count > 0
            ? round($user->completed_orders_total / $user->completed_orders_count, 2)
            : 0;

        return $user;
    });

    return view('admin.users.index', compact('users'));
}

// public function getUsers(Request $request)
// {
//     $perPage = $request->get('per_page', 10);
//     $search = $request->get('search');

//     $query = User::withCount([
//             'orders as completed_orders_count' => function ($q) {
//                 $q->where('status', 'Delivered');
//             }
//         ])
//         ->withSum([
//             'orders as completed_orders_total' => function ($q) {
//                 $q->where('status', 'Delivered');
//             }
//         ], 'total_amount')
//         ->select('id','name','email','phone','postcode','address')
//         ->latest();

//     if ($search) {
//         $query->where(function($q) use ($search){
//             $q->where('name','like',"%$search%")
//               ->orWhere('email','like',"%$search%")
//               ->orWhere('phone','like',"%$search%");
//         });
//     }

//     $users = $query->paginate($perPage);

//     // calculate average spend
//     $users->getCollection()->transform(function ($user) {

//         $user->average_spend = $user->completed_orders_count > 0
//             ? round($user->completed_orders_total / $user->completed_orders_count, 2)
//             : 0;

//         return $user;
//     });

//     $html = view('admin.users.partials.user_rows', compact('users'))->render();

//     return response()->json([
//         'html' => $html,
//         'current_page' => $users->currentPage(),
//         'last_page' => $users->lastPage()
//     ]);
// }
    public function rewards($id){
        $user = User::find($id);
        $userRewards = Reward::where('user_id', $id)->first();
        $remaining = null;
        if ($userRewards) {
            $remaining = $userRewards->rewards - $userRewards->redeemed;
        }
        return view('admin.users.rewards',compact('userRewards' , 'remaining','user'));
    }

    public function destroy($id){
        User::destroy($id);
        return redirect()->back()->with(['status' => true, 'message' => 'Deleted Successfully']);

    }
}
