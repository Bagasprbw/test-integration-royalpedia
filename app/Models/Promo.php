<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $fillable = [
        'kode','potongan','tipe_potongan','kuota','expired_at','status'
    ];
}

