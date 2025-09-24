<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'contact'];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function itemIns()
    {
        return $this->hasMany(Item_in::class, 'supplier_id');
    }

}

