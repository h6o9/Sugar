<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RewardHistoryController extends Controller
{
    //
public function index(Request $request)
{
    $user = auth()->user();

    // Fetch reward histories for the logged-in user
    $rewardHistories = \App\Models\RewardHistory::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    // Map the data to only return the needed fields
    $data = $rewardHistories->map(function($reward) {
        return [
            'reward_title' => $reward->reward_title,
            'reward_description' => $reward->description,
            'reward_type' => $reward->reward_type,
			'points' => $reward->points,
            'created_at' => $reward->created_at->toDateTimeString(),
        ];
    });

    return response()->json([
        'status' => true,
        'message' => 'Reward history fetched successfully',
        'data' => $data
    ], 200);
}

}
