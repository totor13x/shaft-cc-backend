<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

use App\Models\User\Pointshop\PointshopItem as UserPointshopItem;
use App\Models\Server;
use App\Models\User;

class Online extends Model
{
    protected $table = 'user_online';

    protected $casts = [
        'data'          => 'array',
        'start'         => 'datetime',
        'end'           => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}
