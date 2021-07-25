<?php

namespace App\Models\User\Pointshop;

use App\Models\User\Pointshop as UserPointshop;
use App\Models\Economy\Pointshop\PointshopItem as PointshopPointshopItem;
use Illuminate\Database\Eloquent\Model;

class PointshopItem extends Model
{
    protected $table = 'user_pointshop_items';

    protected $casts = [
        'data'   => 'array',
    ];

    protected $fillable = [
        'pointshop_id',
        'pointshop_item_id',
        'data'
    ];

    protected $hidden = [
        'pointshop_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function($item){
            if (is_null($item->data)) {
                $item->data = [];
                $item->save();
            }
        });
    }

    public function pointshop()
    {
        return $this->belongsTo(UserPointshop::class);
    }
    public function pointshop_item()
    {
        return $this->belongsTo(PointshopPointshopItem::class, 'pointshop_item_id', 'id');
    }

}
