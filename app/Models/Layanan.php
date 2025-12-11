<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $fillable = [
        'kategori_id','layanan','provider_id','provider',
        'harga','harga_member','harga_gold','harga_platinum','status'
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }
}

