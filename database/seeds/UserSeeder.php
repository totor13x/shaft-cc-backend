<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_set('memory_limit', '512MB');

        $data = Storage::get('users.json');
        $data = json_decode($data);

        User::query()->delete();
        $i = 0;
        foreach($data as $user) {
            $dat = json_decode($user->data);
            $n = new User;
            $n->steamid = $user->steamid;
            $n->username = $user->nick;
            $n->tag_id = $user->tag;
            $n->api_token = generate_token();
            $n->save();
        }
    }
}
