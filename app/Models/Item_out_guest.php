<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item_out_guest extends Model
{
    protected $fillable = [
        'guest_id',
        'items',
        'printed_at',
    ];

    protected $casts = [
        'items' => 'array',
        'printed_at' => 'datetime',
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function item() 
    {
        return $this->belongsTo(Item::class);
    }
}