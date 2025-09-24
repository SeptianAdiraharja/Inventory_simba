<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guest_carts extends Model
{
    use HasFactory;

    protected $table = 'guest_carts';
    protected $fillable = ['session_id'];


    // relasi belongsToMany ke Item lewat pivot guest_cart_items
    public function items()
    {
        return $this->belongsToMany(Item::class, 'guest_cart_items', 'guest_cart_id', 'item_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class, 'guest_id', 'id');
    }

    // relasi ke tabel pivot langsung
    public function guestCartItems()
    {
        return $this->hasMany(Guest_carts_item::class, 'guest_cart_id', 'id');
    }

}