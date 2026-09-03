<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisTagihan extends Model
{
    protected $table = 'jenis_tagihans';
    protected $guarded = [];

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class);
    }
}
