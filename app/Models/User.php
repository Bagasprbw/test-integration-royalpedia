<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    protected $fillable = [
        'name', 'username', 'email', 'password', 'role', 'balance', 'whatsapp'
    ];

    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'username', 'username');
    }

    public function pembelians()
    {
        return $this->hasMany(Pembelian::class, 'username', 'username');
    }
}

