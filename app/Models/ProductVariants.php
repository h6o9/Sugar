<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariants extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'product_variants';
    public function product(){

        return $this->belongsTo(Product::class ,'product_id' , 'id');
    }
}
