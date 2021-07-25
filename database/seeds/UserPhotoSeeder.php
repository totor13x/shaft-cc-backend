<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserPhotoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_set('memory_limit', '512MB');

        $data = Storage::get('user_photos_a.json');
        $data = json_decode($data);

        DB::table('user_photos')->delete();

        foreach($data as $u) {
            $user = User::whereSteamid($u->sid)->first();
            if ($user) {
                $user->photos()->create([
                    'path' => $u->jpg,
                    'map' => $u->map,
                    'system' => 'old',
                    'created_at' => Carbon::createFromTimeString($u->updated_at),
                ]);
            }
        }
    }
}
