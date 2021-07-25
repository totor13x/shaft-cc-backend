<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Core\Permission;
use App\Models\Core\Role;

class PermissionController extends Controller
{
    protected function show(Request $request) {
        return Permission::with('roles')->get();
    }

    protected function save(Request $request, Permission $permission) {
        // TODO: Сделать валидацию по slug, типа проверки
        // на уникальность и проверки, если slug привязан к какой-либо роли
        $permission->fill($request->data);
        $permission->save();
    }

    protected function create(Request $request) {
        $random = random_strings(5);

        return Permission::create([
            'name' => $random,
            'slug' => $random
        ])->toArray();
    }

    protected function delete(Request $request, $permission_id) {
        // TODO дописать функцию удаления прав
   }
}
