<?php

use App\Models\Core\Role;
use App\Models\Core\RolePermissionServer;
use App\Models\Core\RolePermissionTime;
use App\Models\Economy\Tag;
use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_set('memory_limit', '512MB');

        $role = Role::whereSlug('vip')->first();

        $ocsid = [
            'YANDERERP' => Server::whereIdName('gm_yandererp')->first(),
            'STARWARSRP' => Server::whereIdName('gm_starwarsrp')->first(),
            'PROPHUNT' => Server::whereIdName('gm_prophunt')->first(),
            'CINEMA' => Server::whereIdName('gm_cinema')->first(),
            'DEATHRUN' => Server::whereIdName('gm_deathrun')->first(),
            'MURDER' => Server::whereIdName('gm_murder')->first(),
        ];

        $data = Storage::get('user_groups.json');
        $data = json_decode($data);

        foreach($data as $gr) {
            $sss = new \SteamID($gr->steamid);
            $sss = $sss->ConvertToUInt64();
            $user = User::whereSteamid($sss)->first();
            if ($user) {

                $userRole = $user->roles()->create([
                    'role_id' => $role->id
                ]);
                $aga = new RolePermissionServer();
                $aga->server_id = $ocsid[$gr->serverid]->id;
                $aga->morphable()->associate($userRole);
                $aga->save();

                if ($gr->length != '0') {
                    $agb = new RolePermissionTime();
                    $agb->ended_at = Carbon::createFromTimestamp($gr->start + $gr->length);
                    $agb->morphable()->associate($userRole);
                    $agb->save();
                }
            }
        }
    }
}
