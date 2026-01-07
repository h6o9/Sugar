<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddToCartItemTopping extends Model
{
    use HasFactory;
	protected $guarded = [];

	protected $table = 'add_to_cart_item_toppings';

	 public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id', 'id');
    }

	  public function topping()
    {
        return $this->belongsTo(Topping::class, 'topping_id', 'id');
    }

	
}
