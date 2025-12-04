<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Milon\Barcode\DNS1D;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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

    /* ===========================
       🔗 RELASI ANTAR MODEL
    ============================ */

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

    // 🧩 Relasi tambahan agar bisa ambil supplier melalui Item_in
    public function suppliersThroughIn()
    {
        return $this->hasManyThrough(
            Supplier::class,
            Item_in::class,
            'item_id',     // Foreign key di Item_in
            'id',          // Foreign key di Supplier
            'id',          // Local key di Item
            'supplier_id'  // Local key di Item_in
        );
    }


    /* ===========================
       📊 AKSESOR & HELPER FIELD
    ============================ */

    // Jumlah expired & non-expired (semua supplier)
    public function getExpiredCountAttribute()
    {
        return $this->itemIns->where('expired_at', '<', now())->sum('quantity');
    }

    public function getNonExpiredCountAttribute()
    {
        return $this->itemIns->where('expired_at', '>=', now())->sum('quantity');
    }

    // Jumlah expired & non-expired (berdasarkan supplier)
    public function expiredCountBySupplier($supplierId)
    {
        return $this->itemIns
            ->where('supplier_id', $supplierId)
            ->where('expired_at', '<', now())
            ->sum('quantity');
    }

    public function nonExpiredCountBySupplier($supplierId)
    {
        return $this->itemIns
            ->where('supplier_id', $supplierId)
            ->where('expired_at', '>=', now())
            ->sum('quantity');
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
        if (empty($this->code)) {
            return null;
        }

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

    /* ===========================
       ⚙️ AUTO GENERATE KODE BARANG
    ============================ */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->code)) {
                $item->code = self::generateUniqueCode($item->category_id);
            }
        });

        // Pastikan barcode ada setelah save
        static::saved(function ($item) {
            // Generate barcode PNG dan simpan jika belum ada
            if (empty($item->barcode_png_path)) {
                $item->generateAndSaveBarcode();
            }
        });
    }

    public function generateAndSaveBarcode()
    {
        if (empty($this->code)) {
            return;
        }

        $dns1d = new DNS1D();
        $barcodeImage = $dns1d->getBarcodePNG($this->code, 'C128', 2, 60);

        // Konversi base64 ke binary
        $binaryData = base64_decode($barcodeImage);

        // Path untuk menyimpan barcode
        $directory = 'public/barcodes/' . date('Y/m');
        $filename = 'barcode-' . $this->code . '-' . time() . '.png';
        $path = $directory . '/' . $filename;

        // Buat direktori jika belum ada
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        // Simpan file
        Storage::put($path, $binaryData);

        // Update path barcode di database (tambahkan field di migration jika perlu)
        // Atau Anda bisa simpan di cache/akses langsung dari storage
    }

    public function getBarcodeUrlAttribute()
    {
        return route('item.barcode', ['code' => $this->code]);
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

    /* ===========================
       🛒 GUEST CART RELATIONS
    ============================ */
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
