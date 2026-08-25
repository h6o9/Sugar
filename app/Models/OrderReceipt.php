<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReceipt extends Model
{
    protected $guarded = [];

    protected $casts = [
        'snapshot' => 'array',
        'superseded_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
