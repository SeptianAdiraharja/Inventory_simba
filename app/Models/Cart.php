<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'guest_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // relasi many-to-many ke Item lewat cart_items
    public function items()
    {
        return $this->belongsToMany(Item::class, 'cart_items', 'cart_id', 'item_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    // relasi ke tabel pivot langsung
    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }

    // relasi ke item_outs
    public function itemOuts()
    {
        return $this->hasMany(Item_out::class, 'cart_id');
    }

}
