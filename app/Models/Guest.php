<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'description',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // 🔹 Guest memiliki satu cart
    public function guestCart()
    {
        return $this->hasOne(Guest_carts::class, 'guest_id', 'id');
    }

    // 🔹 Guest bisa memiliki banyak transaksi keluar
    public function itemOutGuests()
    {
        return $this->hasMany(Item_out_guest::class, 'guest_id', 'id');
    }
}
