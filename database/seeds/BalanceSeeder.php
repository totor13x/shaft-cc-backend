<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_set('memory_limit', '512MB');

        $data = Storage::get('balance.json');
        $data = json_decode($data);

        $us = [];
        foreach($data as $bal) {
            if (!isset($us[$bal->steamid])) {
                $us[$bal->steamid] = 0;
            }

            $us[$bal->steamid] += floor($bal->cost);
        }

        DB::table('user_tts_balance')
            ->delete();

        foreach($us as $steam => $u) {
            if ($u == 0) continue;

            $user = User::whereSteamid($steam)->first();
            if ($user) {
                $user->tts_history()->create([
                    'cost' => $u,
                    'type' => 'from_cc',
                ]);
            }
        }
    }
}
