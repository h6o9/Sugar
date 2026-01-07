<?php

namespace App\Http\Controllers\Api;

use App\Models\Faq;
use App\Models\Reward;
use App\Models\Gallery;
use Illuminate\Http\Request;
use App\Models\PrivacyPolicy;
use App\Models\RewardSetting;
use App\Models\TermCondition;
use App\Http\Controllers\Controller;

class PageController extends Controller
{
    //

public function termsAndConditions()
{
    $data = TermCondition::all()->map(function($item) {
        // HTML remove but headings keep as plain text
        $description = $item->description;

        // Convert <h1>, <h2>, <h3>, <strong> to uppercase headings
        $description = preg_replace_callback(
            '/<(h[1-3]|strong)>(.*?)<\/\1>/i',
            function($matches) {
                return "\n\n" . strtoupper(strip_tags($matches[2])) . "\n";
            },
            $description
        );

        // Strip remaining tags
        $description = strip_tags($description);

        // Optional: remove extra spaces or newlines
        $description = preg_replace('/\s+/', ' ', $description);

        return [
            'id' => $item->id,
            'description' => trim($description)
        ];
    });

    return response()->json([
        'status' => true,
        'message' => 'Terms and Conditions fetched successfully',
        'data' => $data
    ], 200);
}


public function privacyPolicy()
{
    $data = PrivacyPolicy::all()->map(function($item) {
        $description = $item->description;

        // Headings (h1, h2, h3, strong) ko preserve karke uppercase text banaye
        $description = preg_replace_callback(
            '/<(h[1-3]|strong)>(.*?)<\/\1>/i',
            function($matches) {
                return "\n\n" . strtoupper(strip_tags($matches[2])) . "\n";
            },
            $description
        );

        // Baaki saari tags remove kar do
        $description = strip_tags($description);

        // Extra spaces aur newlines remove
        $description = preg_replace('/\s+/', ' ', $description);

        return [
            'id' => $item->id,
            'description' => trim($description)
        ];
    });

    return response()->json([
        'status' => true,
        'message' => 'Privacy Policy fetched successfully',
        'data' => $data
    ], 200);
}


public function faq()
{
    $data = Faq::all()->map(function($item) {
        // HTML tags remove karke clean text
        $question = preg_replace('/\s+/', ' ', strip_tags($item->question));
        $answer   = preg_replace('/\s+/', ' ', strip_tags($item->answer));

        return [
            'id'       => $item->id,
            'question' => trim($question),
            'answer'   => trim($answer)
        ];
    });

    return response()->json([
        'status'  => true,
        'message' => 'FAQs fetched successfully',
        'data'    => $data
    ], 200);
}


public function getGalleryImages()
{
    $images = Gallery::select('id', 'image')->get();

    return response()->json([
        'status' => true,
        'message' => 'Gallery images fetched successfully',
        'data' => $images
    ], 200);
}



public function getUserRewardAmount()
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not authenticated',
            'data' => null
        ], 401);
    }

    // 1. Reward point price read karo
    $rewardSetting = RewardSetting::first(); // assuming only 1 row
    $pointPrice = $rewardSetting ? $rewardSetting->price : 0;

    // 2. User ka reward fetch karo, agar nahi hai to create
    $userReward = Reward::firstOrCreate(
        ['user_id' => $user->id],
        ['rewards' => 0]
    );

    // 3. Total amount calculate karo
    $totalAmount = $userReward->rewards * $pointPrice;

    // 4. Response
    return response()->json([
        'status' => true,
        'message' => 'User rewards fetched successfully',
        'data' => [
            'points' => $userReward->rewards,
            'price' => $totalAmount
        ]
    ], 200);
}


}
