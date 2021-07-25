<?php

namespace App\Models\Economy\TTS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

use App\Models\Server;
use App\Models\User;

class TTSItem extends Model
{
    protected $table = 'tts_items';

    protected $casts = [
        'data'   => 'array',
        'triggers'   => 'array',
        'server_id'   => 'array',
        'is_global'   => 'boolean',
    ];
    protected $fillable = [
        'name',
        'description',
        'category',
        'price',
        'is_tradable',
        'is_hidden',
        'is_once',
        'server_id',
        'data',
        'triggers',
        'type',
        'is_global'
    ];

    public function run (User $user, $server_id)
    {
        switch ($this->type) {
            case 'points':
                $user->pointshopAddPoints($server_id, $this->data['points']);

                Redis::publish('pointshop/points', json_encode([
                    'user_id' => $user->id,
                    'server_id' => $server_id
                ]));

				return true;
            break;
            case 'premium_yarp':
                $day = $this->data['day'];
				return false;
            break;
            case 'tts_premium':
                $day = $this->data['day'];

                $initdate = now();

                if (!is_null($user->premium_at)) {
                    $initdate = $user->premium_at;
                }

                $user->premium_at = $initdate->addDays($day);
                $user->save();

                Redis::publish('tts/refresh_premium', json_encode([
                    'user_id' => $user->id
                ]));

				return true;
            break;
            case 'givemoney_yarp':
                $money = $this->data['money'];
				return false;
            break;
            case 'giveboombox':
				return false;
                // $money = $this->data['money'];
            break;
            case 'rolesextra':
				return false;
                // $money = $this->data['money'];
            break;
            case 'rolegive':
                $role = $this->data['role'];
                $day = $this->data['day'];

				return false;
            break;
            case 'pointshop_item':
                $item = $user->pointshopAddItem($server_id, $this->itemable->id);

                Redis::publish('pointshop/add_item', json_encode([
                    'user_id' => $user->id,
                    'server_id' => $server_id,
                    'item_id' => $item->id,
                ]));

                // TODO: Обновить инвентарь на сервере у юзера
				return true;
            break;
            default: break;
        }
    }

    public function itemable()
    {
        return $this->morphTo();
    }

    public function servers()
    {
        return $this->belongsToMany(Server::class, 'tts_items_servers', 'item_id', 'server_id');
    }
}
