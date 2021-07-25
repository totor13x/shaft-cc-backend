<?php

namespace App\Models\User\Craft;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Economy\Craft\CraftItem as EconomyCraftItem;

class CraftItem extends Model
{
    protected $table = 'user_craft_items';  
    protected $fillable = [];

    protected $casts = [
        'data'          => 'array',
        'items'          => 'array',
    ];
    
    public function user ()
    {
        return $this->belongsTo(User::class);
    }
    public function craft_item ()
    {
        return $this->belongsTo(EconomyCraftItem::class);
    }
}
