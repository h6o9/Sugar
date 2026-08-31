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

    public function channelKey(): string
    {
        $type = strtolower((string) ($this->order_type ?? ''));
        $menu = strtolower((string) ($this->menu_type ?? ''));

        if (in_array($type, ['wholesale', 'dessert_wholesale', 'dessert-wholesale'], true)
            || $menu === 'wholesale'
            || !empty($this->wholesale_delivery_date)
        ) {
            return 'wholesale';
        }

        if (in_array($type, ['drive_in', 'drive-in', 'drivein'], true)) {
            return 'drive_in';
        }

        if (in_array($type, ['special', 'pappi_special', 'pappi-special'], true) || $menu === 'special') {
            return 'special';
        }

        foreach ($this->orderItem ?? [] as $item) {
            $product = $item->product ?? null;
            if ($product && \App\Support\MenuCatalog::isSpecial($product->menu ?? null)) {
                return 'special';
            }
        }

        return 'regular';
    }

    public function channelLabel(): string
    {
        switch ($this->channelKey()) {
            case 'wholesale':
                return 'Dessert Wholesale';
            case 'drive_in':
                return 'Drive-In';
            case 'special':
                return 'Sugar Papi Special';
            default:
                return 'Regular Order';
        }
    }

}
