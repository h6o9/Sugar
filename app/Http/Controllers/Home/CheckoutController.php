<?php

namespace App\Http\Controllers\Home;

use App\Models\Branch;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use App\Models\UserTimeSlotes;
use App\Http\Controllers\Controller;
use App\Support\CartCheckout;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
   public function getCheckout(){ 
    $branchess = Branch::all();
    $userId = Auth::guard('user')->id();
    $userTimeSlots = UserTimeSlotes::where('user_id', $userId)
        ->first();
    $timeSlots = TimeSlot::all();
    
    $addingToExisting = false;
    try {
        $addingToExisting = app(\App\Services\OrderLifecycleService::class)
            ->hasActiveAddToOrderSession($userId ? (int) $userId : null);
    } catch (\Throwable $e) {
        $addingToExisting = false;
    }

    $cart = CartCheckout::selected();
    if (empty($cart)) {
        return redirect()->route('my-cart')->with([
            'status' => false,
            'message' => 'Please select at least one item to place the order.',
        ]);
    }
    
    // ✅ Session se tip aur delivery charges lein
    $tip = session('tip', 0);  
    $redeemAmount = session('redeem_amount', 0);
    $redeemPoints = session('redeem_points', 0);
    $deliveryCharges = session('delivery_charge', 0);
    $deliveryDistance = session('delivery_distance', 0);
    
    // ✅ Calculate subtotal from cart
    $subtotal = 0;
    $quantity = 0;
    
    foreach ($cart as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $quantity += $item['quantity'];
    }
    
    // ✅ Get tax from active branch (ya session se)
    $tax = 0;
    foreach ($branchess as $branch) {
        if ($branch->status == 1) {
            $tax = $branch->tax;
            break;
        }
    }
    
    // ✅ Calculate total
    $total = $subtotal + $tip + $deliveryCharges + $tax - $redeemAmount;
    if (session('selected_order_type') === 'drive_in') {
        $drivePercent = (float) (\App\Models\BusinessSetting::getValue('drive_in_discount_percent', 20) ?: 20);
        $driveDiscount = round($subtotal * ($drivePercent / 100), 2);
        $total = max(0, $total - $driveDiscount);
        session(['drive_in_discount' => $driveDiscount]);
    }
    
    // ✅ Debug ke liye log
    \Log::info('Checkout Page Data:', [
        'cart_count' => count($cart),
        'subtotal' => $subtotal,
        'tip' => $tip,
        'delivery_charges' => $deliveryCharges,
        'tax' => $tax,
        'total' => $total
    ]);
    // return ['redeemAmount' => $redeemAmount, 'redeemPoints' => $redeemPoints];
    return view('home.checkout', compact(
        'timeSlots',
        'userTimeSlots',
        'branchess',
        'cart',
        'subtotal',
        'tip',
        'redeemAmount',
        'redeemPoints',
        'deliveryCharges',
        'deliveryDistance',
        'tax',
        'total',
        'quantity',
        'addingToExisting'
    ));
}
}
