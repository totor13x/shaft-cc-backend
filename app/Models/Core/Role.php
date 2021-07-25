<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasRolesAndPermissions;

class Role extends Model
{
    use HasRolesAndPermissions;

    protected $fillable = [
        'name',
        'slug',
        'immunity',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'roles_permissions');
    }
}
