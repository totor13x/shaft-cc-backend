<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
// use App\Traits\HasRolesAndPermissions;
use App\Models\User;
use App\Models\Server;

class RolePermissionTime extends Model
{
    protected $table = 'role_permission_timeable';

    protected $casts = [
        'ended_at' => 'datetime',
    ];

    protected $fillable = [
        'ended_at'
    ];

    public function morphable()
    {
        return $this->morphTo();
    }
}
