<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = ['username','jumlah','metode','status','no_pembayaran'];

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }
}

