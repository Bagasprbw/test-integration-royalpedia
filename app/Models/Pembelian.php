<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $fillable = [
        'order_id','username','layanan','harga','user_id','zone',
        'status','provider_order_id','tipe_transaksi','voucher_id','discount_amount'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }
}

