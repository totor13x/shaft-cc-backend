<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserIp extends Model
{
    protected $table = 'old_user_ips';

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
