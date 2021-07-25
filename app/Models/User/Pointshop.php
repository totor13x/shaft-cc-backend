<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

use App\Models\User\Pointshop\PointshopItem as UserPointshopItem;
use App\Models\Server;
use App\Models\User;

class Pointshop extends Model
{
    protected $table = 'user_pointshops';

    protected $fillable = [
        'server_id',
        'user_id'
    ];
    protected $casts = [
        'data'          => 'array',
        'limit'         => 'array',
    ];

    protected $hidden = [
        'data',
    ];

    protected function castAttribute($key, $value)
    {
        if ($this->getCastType($key) == 'array' && is_null($value)) {
            return [];
        }

        return parent::castAttribute($key, $value);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(UserPointshopItem::class)
            ->whereNull('deleted_at');
    }
    public function itemsWithInventory()
    {
        return $this->hasMany(UserPointshopItem::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
    // Не доработано, так как я подумал что это не нужно
    // public function items()
    // {
    //     $items_id = collect($this->data)
    //                     ->pluck('item_id', );

    //     \App\Economy\PointshopItem::whereIn($items_id);
    // }


    # Статик функции для управления инвентарем
    public static function findPointshopUser($user_id, $server_id) {
        $instance = new Pointshop();
        return
            $instance
                ->with('items')
                ->firstOrCreate([
                    'user_id' => $user_id,
                    'server_id' => $server_id,
                ]);
    }
    static function addPoints($user_id, $server_id, $points)
    {
        return
            tap(
                static::findPointshopUser($user_id, $server_id),
                function($inv) use ($points) {
                    $inv->increment('points', $points);
                }
            );
    }
    static function delPoints($user_id, $server_id, $points)
    {
        return
            tap(
                static::findPointshopUser($user_id, $server_id),
                function($inv) use ($points) {
                    $inv->decrement('points', $points);
                }
            );
    }

    static function addItem($user_id, $server_id, $item_id)
    {
        $inv = static::findPointshopUser($user_id, $server_id);
        $item = new UserPointshopItem();
        $item->data = [];
        $item->pointshop_id = $inv->id;
        $item->pointshop_item_id = $item_id;
        $item->save();
        return $item;
    }
    static function delItem($user_id, $server_id, $id)
    {
        return
            tap(
                static::findPointshopUser($user_id, $server_id),
                function($inv) use ($id) {
                    $item = UserPointshopItem::find($id);
                    $item->delete();
                }
            );
    }
    static function setItem($user_id, $server_id, $id, $data)
    {
        return
            tap(
                static::findPointshopUser($user_id, $server_id),
                function($inv) use ($id, $data) {
                    $item = UserPointshopItem::find($id);

                    $data_temp = collect($item->data);
                    foreach ($data as $key => $value) {
                        $data_temp->put($key, $value);
                        if ($data_temp->contains($key, $value) && $value == false) {
                            $data_temp->pull($key);
                        }
                    }
                    dump($data_temp);
                    $item->data = $data_temp->toArray();
                    $item->save();
                }
            );
    }
}
