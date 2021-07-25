<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasRolesAndPermissions;

class Permission extends Model
{
    use HasRolesAndPermissions;

    protected $fillable = [
        'name',
        'slug'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_permissions');
    }
}
