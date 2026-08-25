<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemToppings extends Model
{
    use HasFactory;
    use Concerns\AssignsNextId;

    public $incrementing = false;
    protected $guarded = [];

    public function toppings()
    {
        return $this->belongsTo(Topping::class, 'topping_id','id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id','id');
    }
    public function orderItemsData()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id','id');
    }

	

}
