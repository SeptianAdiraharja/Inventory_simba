<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guest_carts extends Model
{
    use HasFactory;

    protected $table = 'guest_carts';
    protected $fillable = ['session_id', 'guest_id'];

    // 🔹 Cart punya banyak item (via pivot guest_cart_items)
    public function items()
    {
        return $this->belongsToMany(Item::class, 'guest_cart_items', 'guest_cart_id', 'item_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    // 🔹 Cart dimiliki oleh 1 Guest
    public function guest()
    {
        return $this->belongsTo(Guest::class, 'guest_id', 'id');
    }

    // 🔹 Cart juga bisa diakses langsung ke pivot
    public function guestCartItems()
    {
        return $this->hasMany(Guest_carts_item::class, 'guest_cart_id', 'id');
    }
}
