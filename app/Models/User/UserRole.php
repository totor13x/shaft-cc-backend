<?php

namespace App\Models\User;

use App\Traits\HasRolesAndPermissions;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Core\Role;

class UserRole extends Model
{
    use HasRolesAndPermissions;

    protected $table = 'users_roles';

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

}
