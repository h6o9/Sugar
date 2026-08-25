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

    // FIX: hasOne relation — blade mein ->first() ki zaroorat nahi
    public function complementaryProductSingle()
    {
        return $this->hasOne(ComplementaryProduct::class, 'product_id', 'id');
    }

    // Get the complementary product details
    public function complementaryProductDetails()
    {
        return $this->hasOne(ComplementaryProduct::class, 'product_id', 'id')
                    ->with(['complementary']);
    }

    public function resolvedDisplayPrice(): float
    {
        $variants = $this->relationLoaded('variants') ? $this->variants : $this->variants()->get();
        if ($variants && $variants->count() > 0) {
            $regular = $variants->first(function ($v) {
                return strtolower((string) $v->size) === 'regular' && (float) $v->price > 0;
            });
            if ($regular) {
                return (float) $regular->price;
            }
            $priced = $variants->first(function ($v) {
                return (float) $v->price > 0;
            });
            if ($priced) {
                return (float) $priced->price;
            }
            $orig = $variants->first(function ($v) {
                return (float) ($v->original_price ?? 0) > 0;
            });
            if ($orig) {
                return (float) $orig->original_price;
            }
            return (float) ($variants->first()->price ?? 0);
        }
        $price = (float) ($this->price ?? 0);
        if ($price > 0) {
            return $price;
        }
        return (float) ($this->original_price ?? 0);
    }
}