<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ReferralLinkSetting;
use App\Http\Controllers\Controller;

class ReferralLinkSettingController extends Controller
{
    //

public function referralRewardSettings()
	{
		$data = ReferralLinkSetting::all();
		return view('admin.referalrewardsettings.index', compact('data'));
	}

	public function EditreferralRewardSettings()
	{
		$data = ReferralLinkSetting::first();
		return view('admin.referalrewardsettings.edit', compact('data'));
	}

	public function updateReferralRewardSettings(Request $request, $id)
{
    // Find the record or fail
    $data = ReferralLinkSetting::find($id);
    if (!$data) {
        return redirect()->route('referral-reward-settings')
            ->with(['status' => false, 'message' => 'Record not found']);
    }

    // Update the reward points
    $data->reward_points = $request->reward_points;
    $data->save();

    // Redirect with success message
    return redirect()->route('referral-reward-settings')
        ->with(['status' => true, 'message' => 'Referral reward points updated successfully']);
}


}
