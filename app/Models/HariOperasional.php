<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HariOperasional extends Model
{
    use HasFactory;

    protected $table = 'hari_operasional';
    protected $guarded = ['id'];
}