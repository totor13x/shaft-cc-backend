<?php

namespace App\Models\User;

use App\Models\User;
use App\Models\Server;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'user_inventories';

    protected $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function server()
    {
        return $this->belongsTo(Server::class);
    }
    public function itemable()
    {
        return $this->morphTo();
    }
}
