<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reject extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'name',
        'quantity',
        'description',  
        'condition',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

}
