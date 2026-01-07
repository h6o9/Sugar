<?php

namespace App\Http\Controllers\admin;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function index()
    {
        $orders= Order::with(['orderItem.orderToppings.category', 'orderItem.orderToppings.toppings', 'user', 'orderItem.branch'])->latest()->get();
        return view('admin.orders.index', compact('orders'));
    }
}
