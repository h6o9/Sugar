<?php

namespace App\Http\Controllers\Admin;

use App\Models\Reward;
use Illuminate\Http\Request;
use App\Models\RewardSetting;
use App\Http\Controllers\Controller;

class RewardSetingsController extends Controller
{
    //

	public function RewardSettings()
	{
		$data = RewardSetting::all();
		return view('admin.rewardsettings.index', compact('data'));
	}

	public function EditRewardSettings()
	{
		$data = RewardSetting::first();
		return view('admin.rewardsettings.edit', compact('data'));
	}

	public function updateRewardSettings(Request $request, $id)
{
    // Find the record or fail
    $data = RewardSetting::find($id);
    if (!$data) {
        return redirect()->route('referral-reward-settings')
            ->with(['status' => false, 'message' => 'Record not found']);
    }

    // Update the reward points
    $data->price = $request->price;
    $data->save();

    // Redirect with success message
    return redirect()->route('reward-settings')
        ->with(['status' => true, 'message' => 'Referral reward points updated successfully']);
}


}
