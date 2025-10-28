<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_banned',
        'banned_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function guests()
    {
        return $this->hasMany(Guest::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'created_by');
    }

    public function itemIns()
    {
        return $this->hasMany(Item_in::class, 'created_by');
    }

    public function itemOut()
    {
        return $this->hasMany(Item_out::class, 'approved_by');
    }

    public function exportLogs()
    {
        return $this->hasMany(ExportLog::class, 'super_admin_id');
    }
    public function kopSurats()
    {
        return $this->hasMany(KopSurat::class);
    }

}