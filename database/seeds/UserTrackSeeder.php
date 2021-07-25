<?php

use App\Models\Economy\Track;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserTrackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_set('memory_limit', '512MB');

        $data = Storage::get('user_musics.json');
        $data = json_decode($data);

        Track::query()->delete();

        foreach($data as $u) {
            $user = User::whereSteamid($u->sid)->first();
            if ($user) {
                $shared = [];
                // dump($u);
                if (!empty($u->favor_list)) {
                    $u->favor_list = json_decode($u->favor_list, true);
                    if ($u->favor_list) {
                        foreach ($u->favor_list as $s => $sss) {
                            try {
                                $shass = new \SteamID($s);
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
                }
                $user->tracks()->create([
                    'track_name' => Str::of($u->name)->trim()->substr(0, 128),
                    'track_author' => Str::of($u->author)->trim()->substr(0, 128),
                    'path' => $u->href,
                    'waveform' => null,
                    'user_id' => $user->id,
                    'size' => $u->filesize,
                    'length' => $u->lenght,
                    'shared_user_ids' => $shared,
                    'is_shared' => $u->for_all == 'yes',
                    'is_uploaded' => true,
                    'system' => 'old',
                    'created_at' => Carbon::createFromTimeString($u->updated_at),
                    'updated_at' => Carbon::createFromTimeString($u->updated_at),
                ]);
            }
        }
    }
}
