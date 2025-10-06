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

    // 🔹 1 Guest hanya punya 1 cart
    public function guestCart()
    {
        return $this->hasOne(Guest_carts::class, 'guest_id', 'id');
    }
}
