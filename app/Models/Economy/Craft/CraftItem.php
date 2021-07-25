<?php

namespace App\Models\Economy\Craft;

use Illuminate\Database\Eloquent\Model;

use App\Models\Server;
use DB;

class CraftItem extends Model
{
    protected $table = 'craft_items';
    
    protected $fillable = [ 'name' ];

    protected static function boot()
    {
        parent::boot();

        static::created(function($item){
            foreach(Server::all()->pluck('id') as $srvId) {
                DB::table('pivot_craft_items_fake')
                    ->insert([
                        'craft_item_id' => $item->id,
                        'server_id' => $srvId
                    ]);
            }
        });
    }

    
}
