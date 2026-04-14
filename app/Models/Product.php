<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{ 
    use HasFactory;
    protected $guarded = [];

    public function category()
    {

        return $this->hasMany(ToppingProduct::class, 'product_id', 'id');
    }
    public function menu()
    {

        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }
    public function variants()
    {

        return $this->hasMany(ProductVariants::class, 'product_id', 'id');
    }
    public function complementaryProduct()
    {

        return $this->hasMany(ComplementaryProduct::class, 'product_id', 'id');
    }
}
