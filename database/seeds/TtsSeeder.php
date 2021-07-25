<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Economy\Tag;
use App\Models\Economy\TTS\TTSItem;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TtsSeeder extends Seeder
{
    protected $return = [];
    protected $data = [];

    public function run()
    {
        ini_set('memory_limit', '512MB');

        DB::table('user_tts_items')->truncate();
        TTSItem::truncate();

        $arrayOf = [];

        foreach($this->data as $item) {
            $id = $item['pulum'];
            unset($item['pulum']);
            $arrayOf[$id] = TTSItem::create($item);
        }

        $data = Storage::get('user_shopinv.json');
        $data = json_decode($data);
        foreach($data as $item) {
            $user = User::whereSteamid($item->steamid)->first();
            if ($user) {
                if (!isset($arrayOf[$item->inv])) continue;
                $info = $arrayOf[$item->inv];
                if ($info) {
                    $user->tts_items()->create([
                        'item_id' => $info->id,
                        'is_tradable' => false,
                        'is_activated' => $item->act == '1',
                        'created_at' => Carbon::createFromTimeString($item->buyed),
                        'updated_at' => Carbon::createFromTimeString($item->activated),
                    ]);
                }
            }
        }
    }
}
