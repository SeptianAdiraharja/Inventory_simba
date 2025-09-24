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

    public function itemOuts()
    {
        return $this->hasMany(Item_out::class, 'guest_id'); // pakai guest_id di item_outs
    }

    public function guestCarts()
    {
        return $this->hasMany(Guest_carts::class, 'guest_id');
    }

}