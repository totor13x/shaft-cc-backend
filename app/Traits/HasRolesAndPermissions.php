<?php
namespace App\Traits;

use App\Models\Core\Permission;
use App\Models\Core\Role;

use App\Models\Core\RolePermissionTime;
use App\Models\Server;
use App\Models\Core\RolePermissionServer;
use App\Models\User\UserPermission;
use App\Models\User\UserRole;

trait HasRolesAndPermissions
{
    /**
     * @return mixed
     */
    public function roles()
    {
        return $this->hasMany(UserRole::class);
    }
    /**
     * @return mixed
     */
    public function permissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    public function timeable()
    {
        return $this->morphOne(RolePermissionTime::class, 'morphable');
    }

    public function serverable()
    {
        return $this->morphOne(RolePermissionServer::class, 'morphable');
    }
    /**
     * @param mixed ...$roles
     * @return bool
     */
    public function hasRole(... $roles ) {
        foreach ($roles as $role) {
            if ($this->roles->contains('role.slug', $role)) {
                return true;
            }
        }
        return false;
    }
    /**
     * @param $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        return (bool) $this->permissions->where('permission.slug', $permission)->count();
    }
    /**
     * @param $immunity
     * @return bool
     */
    public function canImmunity($immunity)
    {
        return (bool) (
            optional(
                $this->roles()
                    ->with('role')
                    ->get()
                    ->sortByDesc('role.immunity')
                    ->first()
            )
                ->role
                ->immunity < $immunity
        );
    }

    /**
     * @param $permission
     * @return bool
     */
    public function hasPermissionTo($permission)
    {
    return $this->hasPermission($permission) || $this->hasPermissionThroughRole($permission);
    }
    /**
     * @param $permission
     * @return bool
     */
    public function hasPermissionThroughRole($permission)
    {
        $rolesWithPermissions = $this
            ->roles()
            ->with('role.permissions')
            ->get();

        foreach ($rolesWithPermissions as $role) {
            if ($role->role->permissions->contains('slug', $permission)) {
                return true;
            }
        }

        return false;
    }

    /*

        ЭТО ДОЛЖНО КАСАТЬСЯ ТОЛЬКО КЛАССОВ ROLE, PERMISSION

    */
    /**
     * @param array $permissions
     * @return mixed
     */
    protected function getAllPermissions(array $permissions)
    {
        return Permission::whereIn('slug',$permissions)->get();
    }
    /**
     * @param mixed ...$permissions
     * @return $this
     */
    public function givePermissionsTo( $permissions)
    {
        $permissions = $this->getAllPermissions($permissions);
        if($permissions === null) {
            return $this;
        }
        $this->permissions()->saveMany($permissions);
        return $this;
    }
    /**
     * @param mixed ...$permissions
     * @return $this
     */
    public function deletePermissions( $permissions )
    {
        $permissions = $this->getAllPermissions($permissions);
        $this->permissions()->detach($permissions);
        return $this;
    }
    /**
     * @param mixed ...$permissions
     * @return HasRolesAndPermissions
     */
    public function refreshPermissions( $permissions )
    {
        $this->permissions()->detach();
        return $this->givePermissionsTo($permissions);
    }
}
