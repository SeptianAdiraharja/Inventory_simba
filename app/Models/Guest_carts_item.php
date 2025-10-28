<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guest_carts_item extends Model
{
    use HasFactory;

    protected $table = 'guest_cart_items';
    protected $fillable = ['guest_cart_id', 'item_id', 'quantity', 'released_at'];

    // 🔹 Pivot ke cart utama
    public function guestCart()
    {
        return $this->belongsTo(Guest_carts::class, 'guest_cart_id', 'id');
    }

    // 🔹 Pivot ke item barang
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    // 🔹 Satu item cart bisa punya satu transaksi keluar
    public function itemOutGuest()
    {
        return $this->hasOne(Item_out_guest::class, 'guest_cart_item_id', 'id');
    }
}
