<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $fillable = ['nama','kode','server_id','status'];

    public function layanans()
    {
        return $this->hasMany(Layanan::class);
    }
}

