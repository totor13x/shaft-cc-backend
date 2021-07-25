<?php

namespace App\Models\User\Crate;

use App\Models\Economy\Crate;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CrateHistory extends Model
{
    protected $table = 'user_crates_history';

    public function crate()
    {
        return $this->belongsTo(Crate::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function item()
    {
        return $this->belongsTo(Crate\Item::class, 'crate_items_id', 'id');
    }
}
