<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    use Concerns\AssignsNextId;

    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'add_items_until' => 'datetime',
        'last_modified_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'is_scheduled' => 'boolean',
    ];
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

    public function receipts()
    {
        return $this->hasMany(OrderReceipt::class);
    }

    public function modifications()
    {
        return $this->hasMany(OrderModification::class);
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }

}
