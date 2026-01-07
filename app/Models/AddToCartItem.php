<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddToCartItem extends Model
{
    use HasFactory;

	protected $guarded = [];

	protected $table = 'add_to_cart_items';

	 public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    // Relation with Branch (for tax)
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    // Relation with Toppings
    public function toppings()
    {
        return $this->hasMany(AddToCartItemTopping::class, 'add_to_cart_item_id', 'id')
                    ->with('variant');
    }

	
}

