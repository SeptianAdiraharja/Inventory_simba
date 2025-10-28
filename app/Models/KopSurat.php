<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KopSurat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'logo',
        'nama_instansi',
        'nama_unit',
        'alamat',
        'telepon',
        'email',
        'website',
        'kota',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exportLogs()
    {
        return $this->hasMany(ExportLog::class);
    }
}

