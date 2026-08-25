<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderModification extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
