<?php

namespace App\Models\Economy;

use Illuminate\Database\Eloquent\Model;

use App\Models\Economy\Crate\Item;

class Crate extends Model
{
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
