<?php

use App\Models\Core\Permission;
use App\Models\Core\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::query()->delete();
        Permission::query()->delete();

        $data = Storage::get('roles_permissions.json');
        $data = json_decode($data);

        foreach ($data as $key => $value) {
            $create = Role::create([
                'name' => $value->name,
                'slug' => $value->slug,
                'immunity' => $value->immunity,
            ]);
            $create->permissions()->detach();

            foreach ($value->permissions as $perm) {
                $findPerm = Permission::firstOrCreate([
                    'slug' => $perm->slug,
                    'name' => $perm->name,
                ]);
                $create->permissions()->attach($findPerm);
            }
        }
    }
}
