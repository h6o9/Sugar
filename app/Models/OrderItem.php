<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
    use Concerns\AssignsNextId;

    public $incrementing = false;
    protected $guarded = [];
    public function order()
    {

        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function complementaryProduct()
    {
        return $this->belongsTo(Product::class, 'product_complementary_id');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
    public function orderToppings()
    {
        return $this->hasMany(OrderItemToppings::class,'order_item_id','id');
    }

    public function displaySize(): ?string
    {
        $size = trim((string) ($this->product_size ?? ''));
        if ($size === '' || strcasecmp($size, 'null') === 0 || strcasecmp($size, 'default') === 0) {
            return null;
        }
        return $size;
    }

    public function lineTotal(): float
    {
        return round(((float) $this->product_price) * max(1, (int) $this->quantity), 2);
    }

    public function complimentaryGift(): ?Product
    {
        if ($this->product_complementary_id) {
            if ($this->relationLoaded('complementaryProduct')) {
                return $this->complementaryProduct;
            }
            return $this->complementaryProduct()->first();
        }
        $product = $this->relationLoaded('product') ? $this->product : $this->product()->with('complementaryProductSingle.complementary')->first();
        $link = $product ? $product->complementaryProductSingle : null;
        return $link ? $link->complementary : null;
    }
}
