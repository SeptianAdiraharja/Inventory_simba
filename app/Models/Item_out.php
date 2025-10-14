<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item_out extends Model
{
    protected $table = 'item_outs';

    protected $fillable = [
        'item_id',
        'quantity',
        'cart_id',
        'approved_by',
        'released_at',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getTotalValueRupiahAttribute()
    {
        return 'Rp ' . number_format($this->quantity * ($this->item->price ?? 0), 0, ',', '.');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

}
