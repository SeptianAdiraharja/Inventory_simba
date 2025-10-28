<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportLog extends Model
{
    protected $fillable = [
        'super_admin_id',
        'type',
        'format',
        'file_path',
    ];

    public function super_admin()
    {
        return $this->belongsTo(User::class, 'super_admin_id');
    }

    public function kopSurat()
    {
        return $this->belongsTo(KopSurat::class);
    }

}