<?php

namespace App\Models\User;

use App\Traits\HasRolesAndPermissions;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Core\Permission;

class UserPermission extends Model
{
    use HasRolesAndPermissions;

    protected $table = 'users_permissions';

    public $timestamps = false;

    protected $fillable = [
        'permission_id',
        'user_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

}
