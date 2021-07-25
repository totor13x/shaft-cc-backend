<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Permission;
use App\Models\Core\Role;

class RoleController extends Controller
{
    protected function show(Request $request) {
        $roles = Role::with('permissions')
                    ->get()
                    ->toArray();

        foreach($roles as $key => $role) {
            $temp_form = [];
            foreach ($role['permissions'] as $data) {
                $temp_form[$data['slug']] = true;
            }
            $roles[$key]['permissions'] = $temp_form;
        }

        return [
            'roles' => $roles,
            'permissions' => Permission::get()
        ];
    }

    protected function save(Request $request, Role $role) {
        // TODO: Сделать валидацию по slug, типа проверки
        // на уникальность и проверки, если slug привязан к какой-либо роли
        $role->fill($request->data);
        $role->save();
    }

    protected function link(Request $request, Role $role) {
        // TODO: Сделать валидацию по slug, типа проверки
        // на уникальность и проверки, если slug привязан к какой-либо роли
        // $role->fill($request->data);
        // $role->save();
        $permissions = collect($request->data)
                        ->keys()
                        ->toArray();
        $role->refreshPermissions($permissions);
    }

    protected function create(Request $request) {
        $random = random_strings(5);

        return Role::create([
            'name' => $random,
            'slug' => $random
        ])->toArray();
    }

    protected function delete(Request $request, $role_id) {
        // TODO дописать функцию удаления прав
   }
}
