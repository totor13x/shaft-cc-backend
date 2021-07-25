<?php

use App\Models\Economy\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_set('memory_limit', '512MB');

        $data = Storage::get('chat_chat.json');
        $data = json_decode($data);

        DB::table('chat_globals')->delete();
        foreach($data as $chat) {
            $user = User::whereSteamid($chat->name)->first();
            if ($user) {

                DB::table('chat_globals')
                ->insert([
                    'user_id' => $user->id,
                    'message' => mb_substr($chat->text, 0, 255),
                    'tag_id' => $chat->tag,
                    'created_at' => Carbon::createFromTimestamp($chat->time),
                    'updated_at' => Carbon::createFromTimestamp($chat->time),
                ]);
            }
        }
    }
}
