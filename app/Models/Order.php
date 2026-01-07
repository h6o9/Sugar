<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function orderItem()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function orderAddress()
    {

        return $this->hasOne(OrderAddress::class, 'order_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

}
