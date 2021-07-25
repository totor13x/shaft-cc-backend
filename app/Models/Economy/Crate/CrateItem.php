<?php

namespace App\Models\Economy\Crate;

use Illuminate\Database\Eloquent\Model;

class CrateItem extends Model
{
    protected $table = 'crate_items';
    
    protected $casts = [
        'is_logging' => 'boolean',
        'color' => 'array',
    ];

    public function itemable()
    {
        return $this->morphTo();
    }
}
