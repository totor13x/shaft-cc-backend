<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Economy\Pointshop\PointshopItem;
use App\Models\Economy\Craft\CraftItem;

class Server extends Model
{
    /*
    protected $fillable = [
        'instance',
        'id_name',
        'beautiful_name',
        'short_name',
        'api_token',
        'color'
    ];
    */
    protected $hidden = [
        'api_token',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'color' => 'array',
        'is_colloquy' => 'boolean',
        'is_enabled' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function($srv){
            foreach(CraftItem::all()->pluck('id') as $craftId) {
                DB::table('pivot_craft_items_fake')
                    ->insert([
                        'craft_item_id' => $craftId,
                        'server_id' => $srv->id
                    ]);
            }
        });
    }

    public function pointshop_items()
    {
        //select * from pointshop_items where json_search(server_id, 'one', 'gm_murder') is not null;
        // return PointshopItem::whereJsonContains('server_id', $this->id_name);
        return $this->belongsToMany(PointshopItem::class, 'server_items', 'server_id', 'item_id');
    }

    public static function checksum()
    {
        $tableName = with(new static)->getTable();
        return md5(DB::select(DB::raw(sprintf('select MAX(updated_at) as checksum from %s', $tableName)))[0]->checksum);
    }
}
