<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserShowResource;
use App\Models\Core\Role;
use App\Models\Core\RolePermissionServer;
use App\Models\Core\RolePermissionTime;
use Illuminate\Http\Request;
use App\Models\Economy\Taunt;
use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class UserController extends Controller
{
    public function show(Request $request, User $user) {
        // dump($user);
        $user->load([
            'locks',
            'roles' => function($builder) {
                $builder->with(
                    'serverable.server',
                    'timeable',
                    'role'
                );
            }
        ]);
        $user->loadCount([
            'locks',
            'roles',
        ]);
        return new UserShowResource($user);
    }

    public function showRoles(Request $request, User $user) {
        return $user->roles()
            ->with(['serverable', 'timeable', 'role'])
            ->get();
    }

    public function saveRole(Request $request, User $user) {
        abort_if(
            !$request->date,
            422,
            'No date'
        );
        abort_if(
            !$request->role_id,
            422,
            'No role_id'
        );
        abort_if(
            !$request->server_id,
            422,
            'No server_id'
        );
        $date = Carbon::createFromFormat('d.m.Y', $request->date);
        $date->endOfDay();

        $roleId = $request->get('role_id');
        $serverId = $request->get('server_id');
        // dump($roleId, $serverId, $date);
        $role = $user
            ->roles()
            ->whereHas(
                'serverable',
                function($query) use ($serverId) {
                   return $query->whereServerId($serverId);
                }
            )
            ->first();
        // RolePermissionServer::where('server_id', )

        // dd($role);
        if ($role) {
            abort(422, 'Права на сервере уже есть');
        }

        $role = Role::find($roleId);
        if (!$role) {
            abort(422, 'Выбранной группы не существует');
        }

        $userRole = $user->roles()->create([
            'role_id' => $roleId
        ]);

        $aga = new RolePermissionServer;
        $aga->server_id = $serverId;
        $aga->morphable()->associate($userRole);
        $aga->save();

        $agb = new RolePermissionTime;
        $agb->ended_at = $date;
        $agb->morphable()->associate($userRole);
        $agb->save();

        return response('Gived role', 201);
    }

    public function deleteRole(Request $request, User $user) {
        $userRole = $user
            ->roles()
            ->whereId($request->role_id)
            ->first();

        abort_if(
            !$userRole,
            422,
            'No user_role'
        );

        $userRole->delete();

        return response('Delete role', 200);
    }

    public function roles(Request $request) {
        $immunity = $request->user()->maxImmunity();

        return Role::orderBy('immunity')
            ->get(['id', 'slug', 'name']);
    }
    public function servers(Request $request) {
        // $immunity = $request->user()->maxImmunity();

        return Server::get(['id', 'beautiful_name']);
    }
}
