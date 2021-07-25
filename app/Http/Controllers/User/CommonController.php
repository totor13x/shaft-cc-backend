<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use App\Models\Server;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommonController extends Controller
{	
	public function userInfo(Request $request)
	{
		$user = $request->user();
		$user->load('roles.role.permissions');

		$roles = $user->roles->pluck('role.slug')->unique();
		$rolePermissions = $user->roles->pluck('role.permissions.*.slug')->flatten()->unique();

		$permissions = $user->permissions->pluck('permission.slug');

		$allPermissions = $rolePermissions->merge($permissions)->unique();

		$user = $user->toArray();
		$user['roles'] = $roles;
		$user['permissions'] = $allPermissions;
		return $user;
	}
}