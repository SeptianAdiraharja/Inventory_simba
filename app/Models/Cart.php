<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Item;
use App\Models\CartItem;
use App\Models\Item_out;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'status',
    ];

    // user yang sudah soft delete tetap bisa dibaca
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    // many-to-many ke items melalui cart_items
    public function items()
    {
        return $this->belongsToMany(Item::class, 'cart_items', 'cart_id', 'item_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    // koneksi langsung ke pivot
    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }

    // koneksi ke item_outs
    public function itemOuts()
    {
        return $this->hasMany(Item_out::class, 'cart_id');
    }
}
