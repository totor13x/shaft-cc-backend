<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
// use App\Traits\HasRolesAndPermissions;
use App\Models\User;
use App\Models\Server;

class RolePermissionServer extends Model
{
    protected $table = 'role_permission_servers';

    protected $fillable = [
        'server_id'
    ];

    public function server() {
        return $this->belongsTo(Server::class);
    }

    public function morphable()
    {
        return $this->morphTo();
    }
}
