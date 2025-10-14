<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'item_id',
        'quantity',
        'status',
        'rejection_reason',
        'scanned_at'
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
