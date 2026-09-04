<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    protected $fillable = [
        'guru_id',
        'endpoint',
        'p256dh',
        'auth',
        'user_agent',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
