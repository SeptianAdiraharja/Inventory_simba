<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Milon\Barcode\DNS1D;

class Item extends Model
{
    protected $fillable = [
        'name',
        'code',
        'category_id',
        'stock',
        'price',
        'unit_id',
        'supplier_id',
        'expired_at',
        'created_by',
        'image',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    // === Relasi ===
    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'item_id');
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'cart_items', 'item_id', 'cart_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function itemIns()
    {
        return $this->hasMany(Item_in::class, 'item_id');
    }

    public function itemOuts()
    {
        return $this->hasMany(Item_out::class, 'item_id');
    }

    public function itemOutsguest()
    {
        return $this->hasMany(Item_out_guest::class, 'item_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // === Accessor / Helper ===
    public function getExpiredCountAttribute()
    {
        return $this->itemIns->where('expired_at', '<', now())->sum('quantity');
    }

    public function getNonExpiredCountAttribute()
    {
        return $this->itemIns->where('expired_at', '>=', now())->sum('quantity');
    }

    public function getPriceRupiahAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getBarcodeHtmlAttribute()
    {
        return \Milon\Barcode\Facades\DNS1DFacade::getBarcodeHTML($this->code, 'C128', 2, 60);
    }

    public function getBarcodePngBase64Attribute()
    {
        $dns1d = new DNS1D();
        $png = $dns1d->getBarcodePNG($this->code, 'C128', 2, 60);

        return 'data:image/png;base64,' . $png;
    }

    public function getStatusAttribute()
    {
        if (!$this->expired_at) {
            return 'no expired';
        }
        return $this->expired_at->isFuture() ? 'no expired' : 'expired';
    }

    // === Auto Generate Code ===
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->code)) {
                $item->code = self::generateUniqueCode($item->category_id);
            }
        });
    }

    private static function generateUniqueCode($categoryId)
    {
        $categoryCode = str_pad($categoryId, 3, '0', STR_PAD_LEFT);

        $lastItem = self::where('category_id', $categoryId)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastItem && preg_match('/-(\d+)-/', $lastItem->code, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $randomNumber = mt_rand(100, 999);

        return "{$categoryCode}-{$formattedNumber}-{$randomNumber}";
    }

    // === Guest cart ===
    public function guestCartItems()
    {
        return $this->hasMany(Guest_carts_item::class, 'item_id');
    }

    public function guestCarts()
    {
        return $this->belongsToMany(Guest_carts::class, 'guest_cart_items', 'item_id', 'guest_cart_id')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}
