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
        ->get();

    // Average Spend calculate
    $users->transform(function ($user) {
        $user->average_spend = $user->completed_orders_count > 0
            ? round($user->completed_orders_total / $user->completed_orders_count, 2)
            : 0;
        return $user;
    });

    // ✅ Sort by average_spend descending
    $users = $users->sortByDesc('average_spend')->values();

    return view('admin.users.index', compact('users'));
}
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
