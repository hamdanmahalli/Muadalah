<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushEvent extends Model
{
    protected $fillable = [
        'tag',
        'title',
        'user_agent',
    ];
}