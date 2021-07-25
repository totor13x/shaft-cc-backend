<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

use App\Models\Server;
use App\Models\User;
use App\Models\User\Online as UserOnline;

class OnlineSeeder extends Seeder
{

    public function run()
    {
        ini_set('memory_limit', '512MB');

        $data = Storage::get('user_online.json');
        $data = json_decode($data);

        $ocsid = [
            'YANDERERP' => Server::whereIdName('gm_yandererp')->first(),
            'STARWARSRP' => Server::whereIdName('gm_starwarsrp')->first(),
            'PROPHUNT' => Server::whereIdName('gm_prophunt')->first(),
            'CINEMA' => Server::whereIdName('gm_cinema')->first(),
            'DEATHRUN' => Server::whereIdName('gm_deathrun')->first(),
            'MURDER' => Server::whereIdName('gm_murder')->first(),
        ];
        UserOnline::query()->delete();

        $users = [];
        foreach($data as $online) {
            if (isset($users[$online->steamid])) {
                $user = $users[$online->steamid];
            } else {
                $user = User::whereSteamid($online->steamid)->first();
            }
            if (isset($ocsid[$online->srv]) && $user) {
                $users[$online->steamid] = $user;

                if (empty($online->end) || empty($online->start)) continue;
                $set = new UserOnline;
                $set->start = Carbon::createFromTimestamp($online->start);
                $set->end = Carbon::createFromTimestamp($online->end);
                $set->server_id = $ocsid[$online->srv]->id;
                $set->user_id = $user->id;
                $set->data = [];
                $set->save();
            }
        }
    }
}
