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

    // Relasi ke Guest
    public function guest()
    {
        return $this->belongsTo(Guest::class, 'guest_id', 'id');
    }

    // Ambil semua item dari JSON dengan detail
    public function getParsedItemsAttribute()
    {
        return collect($this->items)->map(function ($item) {
            return [
                'id' => $item['item_id'] ?? null,
                'name' => $item['name'] ?? null,
                'quantity' => $item['quantity'] ?? 0,
            ];
        });
    }
}
