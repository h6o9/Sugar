<?php

namespace App\Http\Controllers\Home;

use App\Models\Branch;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use App\Models\UserTimeSlotes;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
   public function getCheckout(){
    $branchess = Branch::all();
    $userId = Auth::guard('user')->id();
    $userTimeSlots = UserTimeSlotes::where('user_id', $userId)
        ->first();
    $timeSlots = TimeSlot::all();
    
    // ✅ Session se cart data retrieve karein
    $cart = session('cart', []);
    
    // ✅ Session se tip aur delivery charges lein
    $tip = session('tip', 0);
    $deliveryCharges = session('delivery_charges', 0);
    
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
    $total = $subtotal + $tip + $deliveryCharges + $tax;
    
    // ✅ Debug ke liye log
    \Log::info('Checkout Page Data:', [
        'cart_count' => count($cart),
        'subtotal' => $subtotal,
        'tip' => $tip,
        'delivery_charges' => $deliveryCharges,
        'tax' => $tax,
        'total' => $total
    ]);
    
    return view('home.checkout', compact(
        'timeSlots',
        'userTimeSlots',
        'branchess',
        'cart',
        'subtotal',
        'tip',
        'deliveryCharges',
        'tax',
        'total',
        'quantity'
    ));
}
}
