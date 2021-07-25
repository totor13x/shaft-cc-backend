<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserIpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_set('memory_limit', '512MB');

        $data = Storage::get('user_familysharing.json');
        $data = json_decode($data);

        DB::table('old_user_connects')->delete();

        foreach($data as $steam) {
            $shass = new \SteamID($steam->steamid);
            $shass = $shass->ConvertToUInt64();
            $user = User::whereSteamid($shass)->first();
            if ($user) {
                $steam->data = json_decode($steam->data);

                $shared = [];
                if ($steam->data) {
                    foreach ($steam->data as $sss) {
                        try {
                            $shass = new \SteamID($sss);
                            $shass = $shass->ConvertToUInt64();
                            $shass = User::whereSteamid($shass)->first();
                            if ($shass) {
                                $shared[] = $shass->id;
                            }
                        } catch (\Throwable $th) {
                            dump($th);
                        }
                    }
                }
                // }
                DB::table('old_user_connects')
                    ->insert([
                        'user_id' => $user->id,
                        'type' => $steam->type,
                        'relations' => json_encode($shared),
                    ]);
            }
        }
        DB::table('old_user_ips')->delete();

        $data = Storage::get('user_ips.json');
        $data = json_decode($data);

        foreach($data as $steam) {
            $user = User::whereSteamid($steam->sid)->first();
            if ($user) {

                DB::table('old_user_ips')
                    ->insert([
                        'user_id' => $user->id,
                        'ip' => $steam->ip,
                        'nick' => $steam->nick
                    ]);
            }
        }
    }
}
